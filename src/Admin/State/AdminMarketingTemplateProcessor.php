<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingTemplateCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingTemplateUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingTemplate;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Marketing\Models\Template;
use Webkul\Marketing\Repositories\TemplateRepository;

/**
 * Handles POST, PUT, DELETE on AdminMarketingTemplate.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\TemplateController:
 *   store / update / destroy. Events fired:
 *     marketing.templates.create.before / after
 *     marketing.templates.update.before / after
 *     marketing.templates.delete.before / after
 *
 * Permission resolution: Sanctum pattern — read role->permission_type /
 * role->permissions directly. Never calls bouncer().
 *
 * Validation mirrors TemplateController::store / ::update:
 *   - name    required
 *   - status  required|in:active,inactive,draft
 *   - content required
 */
class AdminMarketingTemplateProcessor implements ProcessorInterface
{
    protected const ALLOWED_STATUSES = ['active', 'inactive', 'draft'];

    public function __construct(
        protected TemplateRepository $templateRepository,
        protected AdminMarketingTemplateItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // GraphQL delete — update + delete both carry AdminMarketingTemplateUpdateInput; route by name.
        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingTemplateUpdateInput) {
            $this->assertPermission($admin, 'marketing.communications.email_templates.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        // Create
        if ($data instanceof AdminMarketingTemplateCreateInput
            || ($data instanceof AdminMarketingTemplate && $operation instanceof Post)) {
            $this->assertPermission($admin, 'marketing.communications.email_templates.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        // Update
        if ($data instanceof AdminMarketingTemplateUpdateInput
            || ($data instanceof AdminMarketingTemplate && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.communications.email_templates.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        // REST Delete
        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.communications.email_templates.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    protected function handleCreate(array $input): AdminMarketingTemplate
    {
        $this->validatePayload($input);

        Event::dispatch('marketing.templates.create.before');

        $template = $this->templateRepository->create($input);

        // Re-fetch as Eloquent (repository may return contract).
        $template = Template::find($template->id);

        Event::dispatch('marketing.templates.create.after', $template);

        return $this->itemProvider->mapToDtoPublic($template);
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    protected function handleUpdate(int $id, array $input): AdminMarketingTemplate
    {
        $template = Template::find($id);
        if (! $template) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.template.not-found'));
        }

        $this->validatePayload($input);

        Event::dispatch('marketing.templates.update.before', $id);

        $template = $this->templateRepository->update($input, $id);

        $template = Template::find($id);

        Event::dispatch('marketing.templates.update.after', $template);

        return $this->itemProvider->mapToDtoPublic($template);
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    protected function handleDelete(int $id): array
    {
        $template = Template::find($id);
        if (! $template) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.template.not-found'));
        }

        Event::dispatch('marketing.templates.delete.before', $id);

        try {
            $this->templateRepository->delete($id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.template.delete-failed'),
                500,
            );
        }

        Event::dispatch('marketing.templates.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.marketing.template.deleted')];
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    protected function validatePayload(array $input): void
    {
        $rules = [
            'name'    => ['required', 'string'],
            'status'  => ['required', 'string', 'in:'.implode(',', self::ALLOWED_STATUSES)],
            'content' => ['required', 'string'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    // ─── Permissions ─────────────────────────────────────────────────────────

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.template.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.template.no-permission'));
        }
    }

    // ─── Input resolution ────────────────────────────────────────────────────

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingTemplateCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminMarketingTemplateUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingTemplateUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function normaliseArgs(array $input): array
    {
        unset($input['id']);

        // Only keep fillable fields.
        return array_intersect_key($input, array_flip(['name', 'status', 'content']));
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
