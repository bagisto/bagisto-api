<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
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

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // ── GraphQL delete — both delete and update use AdminCategoryUpdateInput;
        //    route by operation name first.
        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminCategoryUpdateInput) {
            $this->assertPermission($admin, 'catalog.categories.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        // ── Create ────────────────────────────────────────────────────────────
        if ($data instanceof AdminCategoryCreateInput
            || ($data instanceof AdminCategory && $operation instanceof Post)) {
            $this->assertPermission($admin, 'catalog.categories.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        // ── Update ────────────────────────────────────────────────────────────
        if ($data instanceof AdminCategoryUpdateInput
            || ($data instanceof AdminCategory && $operation instanceof Put)) {
            $this->assertPermission($admin, 'catalog.categories.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        // ── REST Delete ───────────────────────────────────────────────────────
        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'catalog.categories.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleCreate(array $input): AdminCategory
    {
        $this->validateCreatePayload($input);

        Event::dispatch('catalog.category.create.before');

        // Provide a locale if missing so the repository's translation logic runs cleanly.
        if (empty($input['locale'])) {
            $input['locale'] = app()->getLocale();
        }

        $category = $this->categoryRepository->create($this->filterRepositoryPayload($input));

        Event::dispatch('catalog.category.create.after', $category);

        return $this->fetchAndMap((int) $category->id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Update
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleUpdate(int $id, array $input): AdminCategory
    {
        $category = Category::find($id);
        if (! $category) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.category.not-found'));
        }

        $this->validateUpdatePayload($input, $id);

        Event::dispatch('catalog.category.update.before', $id);

        if (empty($input['locale'])) {
            $input['locale'] = app()->getLocale();
        }

        $category = $this->categoryRepository->update($this->filterRepositoryPayload($input, true), $id);

        Event::dispatch('catalog.category.update.after', $category);

        return $this->fetchAndMap($id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleDelete(int $id): array
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

        return ['message' => __('bagistoapi::app.admin.category.deleted')];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────────────────────────────────────

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'slug'       => ['required', 'string'],
            'name'       => ['required', 'string'],
            'position'   => ['required', 'integer'],
            'attributes' => ['required', 'array'],
        ];

        if (isset($input['display_mode']) && in_array($input['display_mode'], ['description_only', 'products_and_description'], true)) {
            $rules['description'] = ['required', 'string'];
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        // Slug uniqueness — mirror ProductCategoryUniqueSlug (categories table only,
        // products check skipped for v1 to avoid hard dependency on ProductRepository search engine).
        $slug = $input['slug'] ?? null;
        if ($slug && $this->slugTaken($slug, null)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.slug-unique'), 422);
        }
    }

    protected function validateUpdatePayload(array $input, int $excludeId): void
    {
        $locale = $input['locale'] ?? app()->getLocale();

        $rules = [
            'position'   => ['required', 'integer'],
            'attributes' => ['required', 'array'],
        ];

        // Locale-nested validation, mirroring monolith CategoryRequest update form
        $rules[$locale.'.slug'] = ['required', 'string'];
        $rules[$locale.'.name'] = ['required', 'string'];

        if (isset($input['display_mode']) && in_array($input['display_mode'], ['description_only', 'products_and_description'], true)) {
            $rules[$locale.'.description'] = ['required', 'string'];
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        // Slug uniqueness — exclude self
        $slug = $input[$locale]['slug'] ?? null;
        if ($slug && $this->slugTaken($slug, $excludeId)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.slug-unique'), 422);
        }
    }

    protected function slugTaken(string $slug, ?int $excludeCategoryId): bool
    {
        $q = \DB::table('category_translations')->where('slug', $slug);
        if ($excludeCategoryId !== null) {
            $q->where('category_id', '<>', $excludeCategoryId);
        }

        return $q->limit(1)->exists();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Deletable guard — id===1 or any channel's root_category_id
    // ─────────────────────────────────────────────────────────────────────────

    protected function isCategoryDeletable($category): bool
    {
        if ((int) $category->id === 1) {
            return false;
        }

        $rootIds = $this->channelRepository->pluck('root_category_id')->map(fn ($v) => (int) $v)->all();

        return ! in_array((int) $category->id, $rootIds, true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Permission helper (mirrors AdminAttributeFamilyProcessor — no bouncer())
    // ─────────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────────
    // Input resolution
    // ─────────────────────────────────────────────────────────────────────────

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
            'parentId'        => 'parent_id',
            'displayMode'     => 'display_mode',
            'metaTitle'       => 'meta_title',
            'metaDescription' => 'meta_description',
            'metaKeywords'    => 'meta_keywords',
            'logoPath'        => 'logo_path',
            'bannerPath'      => 'banner_path',
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

    // ─────────────────────────────────────────────────────────────────────────
    // Output mapping
    // ─────────────────────────────────────────────────────────────────────────

    protected function fetchAndMap(int $id): AdminCategory
    {
        $fresh = Category::with(['translations', 'filterableAttributes'])->find($id);

        // Delegate to the item provider's mapping logic so the response shape
        // matches GET /api/admin/catalog/categories/{id}.
        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $fresh);
    }
}
