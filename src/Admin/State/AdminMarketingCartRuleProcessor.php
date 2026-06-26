<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRule;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CartRule\Models\CartRule;
use Webkul\CartRule\Repositories\CartRuleRepository;

/**
 * Handles POST / PUT / DELETE for the AdminMarketingCartRule resource.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Promotions\CartRuleController.
 * Permission resolution: Sanctum admin-role check, never bouncer().
 */
class AdminMarketingCartRuleProcessor implements ProcessorInterface
{
    public const ALLOWED_ACTION_TYPES = ['by_percent', 'by_fixed', 'cart_fixed', 'buy_x_get_y'];

    public function __construct(
        protected CartRuleRepository $repository,
        protected AdminMarketingCartRuleItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingCartRuleUpdateInput) {
            $this->assertPermission($admin, 'marketing.promotions.cart_rules.delete');
            $id = (int) basename((string) $this->resolveUpdateId($data, $context));

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminMarketingCartRuleCreateInput
            || ($data instanceof AdminMarketingCartRule && $operation instanceof Post)) {
            $this->assertPermission($admin, 'marketing.promotions.cart_rules.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($data instanceof AdminMarketingCartRuleUpdateInput
            || ($data instanceof AdminMarketingCartRule && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.promotions.cart_rules.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.promotions.cart_rules.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input, bool $isGraphQL = false): AdminMarketingCartRule|AdminMarketingCartRuleRestDto
    {
        $payload = $this->normalisePayload($input);
        $this->validatePayload($payload, null);

        Event::dispatch('promotions.cart_rule.create.before');
        $cartRule = $this->repository->create($payload);
        Event::dispatch('promotions.cart_rule.create.after', $cartRule);

        return $this->buildResult((int) $cartRule->id, $isGraphQL);
    }

    protected function handleUpdate(int $id, array $input, bool $isGraphQL = false): AdminMarketingCartRule|AdminMarketingCartRuleRestDto
    {
        $existing = $this->repository->find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule.not-found'));
        }

        $current = [
            'name'                      => $existing->name,
            'description'               => $existing->description,
            'channels'                  => $existing->cart_rule_channels->pluck('id')->all(),
            'customer_groups'           => $existing->cart_rule_customer_groups->pluck('id')->all(),
            'starts_from'               => $existing->starts_from ? (string) $existing->starts_from : null,
            'ends_till'                 => $existing->ends_till ? (string) $existing->ends_till : null,
            'status'                    => (int) $existing->status,
            'coupon_type'               => (int) $existing->coupon_type,
            'use_auto_generation'       => (int) $existing->use_auto_generation,
            'coupon_code'               => DB::table('cart_rule_coupons')
                ->where('cart_rule_id', $id)
                ->where('is_primary', 1)
                ->value('code'),
            'usage_per_customer'        => (int) $existing->usage_per_customer,
            'uses_per_coupon'           => (int) $existing->uses_per_coupon,
            'condition_type'            => (int) $existing->condition_type,
            'conditions'                => is_array($existing->conditions) ? $existing->conditions : [],
            'action_type'               => $existing->action_type,
            'discount_amount'           => (float) $existing->discount_amount,
            'discount_quantity'         => (int) $existing->discount_quantity,
            'discount_step'             => (string) $existing->discount_step,
            'apply_to_shipping'         => (int) $existing->apply_to_shipping,
            'free_shipping'             => (int) $existing->free_shipping,
            'end_other_rules'           => (int) $existing->end_other_rules,
            'uses_attribute_conditions' => (int) $existing->uses_attribute_conditions,
            'sort_order'                => (int) $existing->sort_order,
        ];

        $merged = array_merge($current, array_filter($input, fn ($v) => $v !== null));

        $merged['channels'] = $merged['channels'] ?? [];
        $merged['customer_groups'] = $merged['customer_groups'] ?? [];
        $merged['conditions'] = $merged['conditions'] ?? [];

        $this->validatePayload($merged, $id);

        Event::dispatch('promotions.cart_rule.update.before', $id);
        $cartRule = $this->repository->update($merged, $id);
        Event::dispatch('promotions.cart_rule.update.after', $cartRule);

        return $this->buildResult($id, $isGraphQL);
    }

    /**
     * Build the write response: GraphQL → the AdminMarketingCartRule Eloquent
     * model (channels / customerGroups connections resolve), the flat RestDto for
     * REST (channels / customer_groups as object arrays).
     */
    protected function buildResult(int $id, bool $isGraphQL): AdminMarketingCartRule|AdminMarketingCartRuleRestDto
    {
        if ($isGraphQL) {
            return AdminMarketingCartRule::with(['channels', 'customer_groups'])->find($id);
        }

        $fresh = CartRule::with(['cart_rule_channels', 'cart_rule_customer_groups', 'coupon_code'])->find($id);

        return $this->itemProvider->buildRestDtoPublic($fresh);
    }

    protected function handleDelete(int $id): array
    {
        $existing = $this->repository->find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule.not-found'));
        }

        try {
            Event::dispatch('promotions.cart_rule.delete.before', $id);
            $this->repository->delete($id);
            Event::dispatch('promotions.cart_rule.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.cart-rule.delete-failed'), 500);
        }

        return ['message' => __('bagistoapi::app.admin.marketing.cart-rule.deleted')];
    }

    protected function validatePayload(array $input, ?int $excludeId): void
    {
        $rules = [
            'name'            => ['required', 'string'],
            'channels'        => ['required', 'array', 'min:1'],
            'customer_groups' => ['required', 'array', 'min:1'],
            'coupon_type'     => ['required', 'in:0,1'],
            'starts_from'     => ['nullable', 'date'],
            'ends_till'       => ['nullable', 'date', 'after_or_equal:starts_from'],
            'action_type'     => ['required', 'in:'.implode(',', self::ALLOWED_ACTION_TYPES)],
            'discount_amount' => ['required', 'numeric'],
        ];

        if (($input['action_type'] ?? null) === 'by_percent') {
            $rules['discount_amount'] = ['required', 'numeric', 'min:0', 'max:100'];
        }

        if ((int) ($input['coupon_type'] ?? 0) === 1 && (int) ($input['use_auto_generation'] ?? 0) === 0) {
            $uniqueRule = 'unique:cart_rule_coupons,code';
            if ($excludeId !== null) {
                $primaryCouponId = DB::table('cart_rule_coupons')
                    ->where('cart_rule_id', $excludeId)
                    ->where('is_primary', 1)
                    ->value('id');
                if ($primaryCouponId) {
                    $uniqueRule .= ','.$primaryCouponId;
                }
            }
            $rules['coupon_code'] = ['required', 'string', $uniqueRule];
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function normalisePayload(array $input): array
    {
        $out = $input;

        $out['channels'] = $out['channels'] ?? [];
        $out['customer_groups'] = $out['customer_groups'] ?? [];
        $out['conditions'] = $out['conditions'] ?? [];
        $out['coupon_type'] = (int) ($out['coupon_type'] ?? 1);
        $out['use_auto_generation'] = (int) ($out['use_auto_generation'] ?? 0);
        $out['starts_from'] = $out['starts_from'] ?? null;
        $out['ends_till'] = $out['ends_till'] ?? null;
        $out['discount_amount'] = isset($out['discount_amount']) ? (float) $out['discount_amount'] : 0.0;

        if (isset($out['status']) && (int) $out['status'] === 0) {
            unset($out['status']);
        }

        return $out;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingCartRuleCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminMarketingCartRuleUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingCartRuleUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map camelCase GraphQL args to snake_case the validator expects.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'customerGroups'           => 'customer_groups',
            'startsFrom'               => 'starts_from',
            'endsTill'                 => 'ends_till',
            'couponType'               => 'coupon_type',
            'useAutoGeneration'        => 'use_auto_generation',
            'couponCode'               => 'coupon_code',
            'usagePerCustomer'         => 'usage_per_customer',
            'usesPerCoupon'            => 'uses_per_coupon',
            'conditionType'            => 'condition_type',
            'actionType'               => 'action_type',
            'discountAmount'           => 'discount_amount',
            'discountQuantity'         => 'discount_quantity',
            'discountStep'             => 'discount_step',
            'applyToShipping'          => 'apply_to_shipping',
            'freeShipping'             => 'free_shipping',
            'endOtherRules'            => 'end_other_rules',
            'usesAttributeConditions'  => 'uses_attribute_conditions',
            'sortOrder'                => 'sort_order',
        ];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $snakeKey = $camelToSnake[$key] ?? $key;
            $result[$snakeKey] = $value;
        }

        foreach (get_object_vars($dto) as $key => $value) {
            if ($value !== null && ! array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
