<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCategoryMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCategoryMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Category\Models\Category;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Repositories\ChannelRepository;

/**
 * POST /api/admin/catalog/categories/mass-delete + createAdminCategoryMassDelete.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\CategoryController::massDestroy:
 *   - For every ID, check the same isCategoryDeletable() guard
 *     (id===1 || channel.root_category_id contains id)
 *   - If any is non-deletable, reject the WHOLE batch with HTTP 400.
 *   - Otherwise delete each one and fire the standard before/after events.
 */
class AdminCategoryMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ChannelRepository $channelRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'catalog.categories.delete');

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.mass-delete-indices-required'), 422);
        }

        // Pre-validate the whole batch: if any ID is non-deletable, reject everything.
        $rootIds = $this->channelRepository->pluck('root_category_id')->map(fn ($v) => (int) $v)->all();

        foreach ($indices as $index) {
            $id = (int) $index;
            $category = Category::find($id);

            if (! $category) {
                continue; // silently skip non-existent
            }

            if ($id === 1 || in_array($id, $rootIds, true)) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.category.cannot-delete-root'),
                    400,
                );
            }
        }

        $deleted = [];

        foreach ($indices as $index) {
            $id = (int) $index;
            $category = Category::find($id);

            if (! $category) {
                continue;
            }

            try {
                Event::dispatch('catalog.category.delete.before', $id);

                $this->categoryRepository->delete($id);

                Event::dispatch('catalog.category.delete.after', $id);

                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.category.delete-failed'),
                    500,
                );
            }
        }

        $result = new AdminCategoryMassDelete;
        $result->id = 1; // placeholder — serialiser needs a non-null id for IRI generation
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.category.mass-delete-success');

        return $result;
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

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminCategoryMassDeleteInput && ! empty($data->indices)) {
            return $data->indices;
        }

        $fromArgs = $context['args']['input']['indices']
            ?? $context['args']['indices']
            ?? null;

        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $fromBody = request()->input('indices');
        if (is_array($fromBody)) {
            return $fromBody;
        }

        return [];
    }
}
