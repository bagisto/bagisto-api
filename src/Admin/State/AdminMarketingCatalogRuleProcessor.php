<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCatalogRule;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CatalogRule\Models\CatalogRule;
use Webkul\CatalogRule\Repositories\CatalogRuleRepository;

/**
 * Handles POST, PUT, DELETE on AdminMarketingCatalogRule.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Promotions\CatalogRuleController:
 *   store / update / destroy. Events fired:
 *     promotions.catalog_rule.create.before / after
 *     promotions.catalog_rule.update.before / after
 *     promotions.catalog_rule.delete.before / after
 *
 * Permission resolution: Sanctum pattern — read role->permission_type /
 * role->permissions directly. Never calls bouncer().
 *
 * Validation mirrors Webkul\Admin\Http\Requests\CatalogRuleRequest:
 *   - name             required
 *   - channels         required|array|min:1
 *   - customer_groups  required|array|min:1
 *   - starts_from      nullable|date
 *   - ends_till        nullable|date|after_or_equal:starts_from
 *   - action_type      required|in:by_percent,by_fixed,to_percent,to_fixed
 *   - discount_amount  required|numeric|min:0  (max 100 when action_type=by_percent)
 */
class AdminMarketingCatalogRuleProcessor implements ProcessorInterface
{
    protected const ALLOWED_ACTION_TYPES = ['by_percent', 'by_fixed', 'to_percent', 'to_fixed'];

    public function __construct(
        protected CatalogRuleRepository $catalogRuleRepository,
        protected AdminMarketingCatalogRuleItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingCatalogRuleUpdateInput) {
            $this->assertPermission($admin, 'marketing.promotions.catalog_rules.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminMarketingCatalogRuleCreateInput
            || ($data instanceof AdminMarketingCatalogRule && $operation instanceof Post)) {
            $this->assertPermission($admin, 'marketing.promotions.catalog_rules.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($data instanceof AdminMarketingCatalogRuleUpdateInput
            || ($data instanceof AdminMarketingCatalogRule && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.promotions.catalog_rules.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.promotions.catalog_rules.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input, bool $isGraphQL = false): AdminMarketingCatalogRule|AdminMarketingCatalogRuleRestDto
    {
        $this->validatePayload($input);

        Event::dispatch('promotions.catalog_rule.create.before');

        $rule = $this->catalogRuleRepository->create($input);

        $rule = CatalogRule::with(['channels', 'customer_groups'])->find($rule->id);

        Event::dispatch('promotions.catalog_rule.create.after', $rule);

        return $this->buildResult((int) $rule->id, $isGraphQL);
    }

    protected function handleUpdate(int $id, array $input, bool $isGraphQL = false): AdminMarketingCatalogRule|AdminMarketingCatalogRuleRestDto
    {
        $rule = CatalogRule::with(['channels', 'customer_groups'])->find($id);
        if (! $rule) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.catalog-rule.not-found'));
        }

        $current = [
            'name' => $rule->name,
            'description' => $rule->description,
            'channels' => $rule->channels->pluck('id')->map(fn ($v) => (int) $v)->all(),
            'customer_groups' => $rule->customer_groups->pluck('id')->map(fn ($v) => (int) $v)->all(),
            'starts_from' => $rule->starts_from ? (string) $rule->starts_from : null,
            'ends_till' => $rule->ends_till ? (string) $rule->ends_till : null,
            'status' => (int) $rule->status,
            'condition_type' => (int) $rule->condition_type,
            'conditions' => is_array($rule->conditions) ? $rule->conditions : [],
            'end_other_rules' => (int) $rule->end_other_rules,
            'action_type' => $rule->action_type,
            'discount_amount' => (float) $rule->discount_amount,
            'sort_order' => (int) $rule->sort_order,
        ];

        $merged = array_merge($current, array_filter($input, fn ($v) => $v !== null));
        $merged['channels'] = $merged['channels'] ?? [];
        $merged['customer_groups'] = $merged['customer_groups'] ?? [];
        $merged['conditions'] = $merged['conditions'] ?? [];

        $this->validatePayload($merged);

        Event::dispatch('promotions.catalog_rule.update.before', $id);

        $rule = $this->catalogRuleRepository->update($merged, $id);

        $rule = CatalogRule::with(['channels', 'customer_groups'])->find($id);

        Event::dispatch('promotions.catalog_rule.update.after', $rule);

        return $this->buildResult($id, $isGraphQL);
    }

    /**
     * Build the write response: GraphQL → the AdminMarketingCatalogRule Eloquent
     * model (channels / customerGroups connections resolve), the flat RestDto for
     * REST (channels / customer_groups as object arrays).
     */
    protected function buildResult(int $id, bool $isGraphQL): AdminMarketingCatalogRule|AdminMarketingCatalogRuleRestDto
    {
        if ($isGraphQL) {
            return AdminMarketingCatalogRule::with(['channels', 'customer_groups'])->find($id);
        }

        $fresh = CatalogRule::with(['channels', 'customer_groups'])->find($id);

        return $this->itemProvider->buildRestDtoPublic($fresh);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminMarketingCatalogRule
    {
        $rule = CatalogRule::find($id);
        if (! $rule) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.catalog-rule.not-found'));
        }

        $snapshot = $asResource ? AdminMarketingCatalogRule::find($id) : null;

        Event::dispatch('promotions.catalog_rule.delete.before', $id);

        try {
            $this->catalogRuleRepository->delete($id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.catalog-rule.delete-failed'),
                500,
            );
        }

        Event::dispatch('promotions.catalog_rule.delete.after', $id);

        if ($asResource && $snapshot) {
            $snapshot->actionMessage = __('bagistoapi::app.admin.marketing.catalog-rule.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.marketing.catalog-rule.deleted')];
    }

    protected function validatePayload(array $input): void
    {
        $rules = [
            'name' => ['required', 'string'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['integer'],
            'customer_groups' => ['required', 'array', 'min:1'],
            'customer_groups.*' => ['integer'],
            'starts_from' => ['nullable', 'date'],
            'ends_till' => ['nullable', 'date', 'after_or_equal:starts_from'],
            'action_type' => ['required', 'string', 'in:'.implode(',', self::ALLOWED_ACTION_TYPES)],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'conditions' => ['nullable', 'array'],
        ];

        if (($input['action_type'] ?? null) === 'by_percent') {
            $rules['discount_amount'] = ['required', 'numeric', 'min:0', 'max:100'];
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.catalog-rule.no-permission'));
        }

        if (($role->permission_type ?? null) === 'all') {
            return;
        }

        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }

        if (! in_array($permission, $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.catalog-rule.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingCatalogRuleCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminMarketingCatalogRuleUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingCatalogRuleUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    /**
     * Strip nulls and ignore unsupported fields. Map camelCase variants → snake.
     */
    protected function normaliseArgs(array $input): array
    {
        $camelToSnake = [
            'startsFrom' => 'starts_from',
            'endsTill' => 'ends_till',
            'sortOrder' => 'sort_order',
            'conditionType' => 'condition_type',
            'endOtherRules' => 'end_other_rules',
            'actionType' => 'action_type',
            'discountAmount' => 'discount_amount',
            'customerGroups' => 'customer_groups',
        ];

        foreach ($camelToSnake as $camel => $snake) {
            if (array_key_exists($camel, $input) && ! array_key_exists($snake, $input)) {
                $input[$snake] = $input[$camel];
            }
            unset($input[$camel]);
        }

        unset($input['id']);

        return $input;
    }

    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result[$key] = $value;
        }

        foreach (get_object_vars($dto) as $key => $value) {
            if ($value !== null && ! array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
