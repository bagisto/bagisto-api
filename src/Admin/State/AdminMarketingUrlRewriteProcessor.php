<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingUrlRewriteCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingUrlRewriteUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingUrlRewrite;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Marketing\Models\URLRewrite;
use Webkul\Marketing\Repositories\URLRewriteRepository;

/**
 * Handles POST, PUT, DELETE on AdminMarketingUrlRewrite.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\URLRewriteController:
 *   store / update / destroy. Events fired:
 *     marketing.search_seo.url_rewrites.create.before / after
 *     marketing.search_seo.url_rewrites.update.before / after
 *     marketing.search_seo.url_rewrites.delete.before / after
 *
 * Permission resolution: Sanctum pattern — read role->permission_type /
 * role->permissions directly. Never calls bouncer().
 *
 * Validation mirrors URLRewriteController::store / ::update:
 *   - entity_type   required|in:product,category,cms_page
 *   - request_path  required
 *   - target_path   required
 *   - redirect_type required|in:301,302
 *   - locale        required|exists:locales,code
 */
class AdminMarketingUrlRewriteProcessor implements ProcessorInterface
{
    protected const ALLOWED_ENTITY_TYPES = ['product', 'category', 'cms_page'];

    protected const ALLOWED_REDIRECT_TYPES = ['301', '302'];

    public function __construct(
        protected URLRewriteRepository $urlRewriteRepository,
        protected AdminMarketingUrlRewriteItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingUrlRewriteUpdateInput) {
            $this->assertPermission($admin, 'marketing.search_seo.url_rewrites.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminMarketingUrlRewriteCreateInput
            || ($data instanceof AdminMarketingUrlRewrite && $operation instanceof Post)) {
            $this->assertPermission($admin, 'marketing.search_seo.url_rewrites.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminMarketingUrlRewriteUpdateInput
            || ($data instanceof AdminMarketingUrlRewrite && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.search_seo.url_rewrites.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.search_seo.url_rewrites.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminMarketingUrlRewrite
    {
        $this->validatePayload($input);

        Event::dispatch('marketing.search_seo.url_rewrites.create.before');

        $rewrite = $this->urlRewriteRepository->create($input);

        $rewrite = URLRewrite::find($rewrite->id);

        Event::dispatch('marketing.search_seo.url_rewrites.create.after', $rewrite);

        return $this->itemProvider->mapToDtoPublic($rewrite);
    }

    protected function handleUpdate(int $id, array $input): AdminMarketingUrlRewrite
    {
        $rewrite = URLRewrite::find($id);
        if (! $rewrite) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.url-rewrite.not-found'));
        }

        $this->validatePayload($input);

        Event::dispatch('marketing.search_seo.url_rewrites.update.before', $id);

        $rewrite = $this->urlRewriteRepository->update($input, $id);

        $rewrite = URLRewrite::find($id);

        Event::dispatch('marketing.search_seo.url_rewrites.update.after', $rewrite);

        return $this->itemProvider->mapToDtoPublic($rewrite);
    }

    protected function handleDelete(int $id): array
    {
        $rewrite = URLRewrite::find($id);
        if (! $rewrite) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.url-rewrite.not-found'));
        }

        Event::dispatch('marketing.search_seo.url_rewrites.delete.before', $id);

        try {
            $this->urlRewriteRepository->delete($id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.url-rewrite.delete-failed'),
                500,
            );
        }

        Event::dispatch('marketing.search_seo.url_rewrites.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.marketing.url-rewrite.deleted')];
    }

    protected function validatePayload(array $input): void
    {
        $rules = [
            'entity_type'   => ['required', 'string', 'in:'.implode(',', self::ALLOWED_ENTITY_TYPES)],
            'request_path'  => ['required', 'string'],
            'target_path'   => ['required', 'string'],
            'redirect_type' => ['required', 'in:'.implode(',', self::ALLOWED_REDIRECT_TYPES)],
            'locale'        => ['required', 'string', 'exists:locales,code'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.url-rewrite.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.url-rewrite.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingUrlRewriteCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminMarketingUrlRewriteUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingUrlRewriteUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function normaliseArgs(array $input): array
    {
        unset($input['id']);

        $camelToSnake = [
            'entityType'   => 'entity_type',
            'requestPath'  => 'request_path',
            'targetPath'   => 'target_path',
            'redirectType' => 'redirect_type',
        ];

        foreach ($camelToSnake as $camel => $snake) {
            if (array_key_exists($camel, $input) && ! array_key_exists($snake, $input)) {
                $input[$snake] = $input[$camel];
            }
            unset($input[$camel]);
        }

        if (isset($input['redirect_type'])) {
            $input['redirect_type'] = (string) $input['redirect_type'];
        }

        return array_intersect_key($input, array_flip([
            'entity_type', 'request_path', 'target_path', 'redirect_type', 'locale',
        ]));
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
