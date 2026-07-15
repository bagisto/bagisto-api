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
use Webkul\BagistoApi\Admin\Dto\AdminCategoryCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCategoryUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCategory;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Category\Models\Category;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Repositories\ChannelRepository;

/**
 * Handles POST, PUT, DELETE on the AdminCategory resource (Phase 2 — CRUD).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\CategoryController::
 *   store() / update() / destroy()
 * — validation, events, and the isCategoryDeletable guard (id===1 || channel root).
 *
 * Permission resolution mirrors AdminAttributeFamilyProcessor: read
 * role->permission_type / role->permissions directly. Never call bouncer()
 * (Sanctum-token requests have no session-bound admin).
 */
class AdminCategoryProcessor implements ProcessorInterface
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ChannelRepository $channelRepository,
        protected AdminCategoryItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminCategoryUpdateInput) {
            $this->assertPermission($admin, 'catalog.categories.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminCategoryCreateInput
            || ($data instanceof AdminCategory && $operation instanceof Post)) {
            $this->assertPermission($admin, 'catalog.categories.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminCategoryUpdateInput
            || ($data instanceof AdminCategory && $operation instanceof Put)) {
            $this->assertPermission($admin, 'catalog.categories.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'catalog.categories.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminCategory
    {
        $this->validateCreatePayload($input);

        Event::dispatch('catalog.category.create.before');

        if (empty($input['locale'])) {
            $input['locale'] = app()->getLocale();
        }

        $category = $this->categoryRepository->create($this->filterRepositoryPayload($input));

        Event::dispatch('catalog.category.create.after', $category);

        return $this->fetchAndMap((int) $category->id);
    }

    protected function handleUpdate(int $id, array $input): AdminCategory
    {
        $category = Category::find($id);
        if (! $category) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.category.not-found'));
        }

        $this->validateUpdatePayload($input, $id);

        if (empty($input['locale'])) {
            $input['locale'] = app()->getLocale();
        }

        request()->merge($input);

        Event::dispatch('catalog.category.update.before', $id);

        $category = $this->categoryRepository->update($this->filterRepositoryPayload($input, true), $id);

        Event::dispatch('catalog.category.update.after', $category);

        return $this->fetchAndMap($id);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminCategory
    {
        $category = Category::find($id);
        if (! $category) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.category.not-found'));
        }

        if (! $this->isCategoryDeletable($category)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.category.cannot-delete-root'),
                400,
            );
        }

        $snapshot = $asResource
            ? $this->mapModel(Category::with(['translations', 'filterableAttributes'])->find($id))
            : null;

        try {
            Event::dispatch('catalog.category.delete.before', $id);

            $this->categoryRepository->delete($id);

            Event::dispatch('catalog.category.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.category.delete-failed'),
                500,
            );
        }

        if ($snapshot) {
            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.category.deleted')];
    }

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'slug' => ['required', 'string'],
            'name' => ['required', 'string'],
            'position' => ['required', 'integer'],
            'attributes' => ['required', 'array'],
        ];

        if (isset($input['display_mode']) && in_array($input['display_mode'], ['description_only', 'products_and_description'], true)) {
            $rules['description'] = ['required', 'string'];
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $slug = $input['slug'] ?? null;
        if ($slug && ! $this->categoryRepository->isSlugUnique(0, $slug)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.slug-unique'), 422);
        }
    }

    protected function validateUpdatePayload(array $input, int $excludeId): void
    {
        $locale = $input['locale'] ?? app()->getLocale();

        $rules = [
            'position' => ['required', 'integer'],
            'attributes' => ['required', 'array'],
        ];

        $rules[$locale.'.slug'] = ['required', 'string'];
        $rules[$locale.'.name'] = ['required', 'string'];

        if (isset($input['display_mode']) && in_array($input['display_mode'], ['description_only', 'products_and_description'], true)) {
            $rules[$locale.'.description'] = ['required', 'string'];
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $slug = $input[$locale]['slug'] ?? null;
        if ($slug && ! $this->categoryRepository->isSlugUnique($excludeId, $slug)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.slug-unique'), 422);
        }
    }

    protected function isCategoryDeletable($category): bool
    {
        if ((int) $category->id === 1) {
            return false;
        }

        $rootIds = $this->channelRepository->pluck('root_category_id')->map(fn ($v) => (int) $v)->all();

        return ! in_array((int) $category->id, $rootIds, true);
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.category.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.category.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminCategoryCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminCategoryUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminCategoryUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map GraphQL camelCase args to the snake_case form the validator + repository expect.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'parentId' => 'parent_id',
            'displayMode' => 'display_mode',
            'metaTitle' => 'meta_title',
            'metaDescription' => 'meta_description',
            'metaKeywords' => 'meta_keywords',
            'logoPath' => 'logo_path',
            'bannerPath' => 'banner_path',
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

    /**
     * Strip API-only keys; pass through everything the repository understands.
     * For updates, keep the locale-nested block.
     */
    protected function filterRepositoryPayload(array $input, bool $isUpdate = false): array
    {
        unset($input['id']);

        return $input;
    }

    protected function fetchAndMap(int $id): AdminCategory
    {
        return $this->mapModel(Category::with(['translations', 'filterableAttributes'])->find($id));
    }

    protected function mapModel(object $model): AdminCategory
    {
        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $model);
    }
}
