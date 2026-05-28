<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductCopyInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductCopy;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Repositories\ProductRepository;

/**
 * POST /api/admin/catalog/products/{sourceId}/copy + createAdminCatalogProductCopy.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\ProductController::copy:
 *   - Fire catalog.product.create.before
 *   - $productRepository->copy($id)
 *   - Fire catalog.product.create.after
 *
 * ProductRepository::copy throws if $product->parent_id is set (variants
 * cannot be copied); we surface that as HTTP 422 with a specific lang key.
 * Anything else from the repository surfaces as HTTP 500.
 *
 * Permission gate: catalog.products.create (Sanctum-pattern; reads
 * $admin->role->permission_type / ->permissions directly — never bouncer()).
 */
class AdminCatalogProductCopyProcessor implements ProcessorInterface
{
    public function __construct(
        protected ProductRepository $productRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'catalog.products.create');

        $sourceId = $this->resolveSourceId($data, $uriVariables, $context);

        if ($sourceId <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $source = $this->productRepository->find($sourceId);
        if (! $source) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        if (! empty($source->parent_id)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.copy-variant-not-supported'),
                422,
            );
        }

        try {
            Event::dispatch('catalog.product.create.before');

            $copy = $this->productRepository->copy($sourceId);

            Event::dispatch('catalog.product.create.after', $copy);
        } catch (InvalidInputException|ResourceNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            $msg = $e->getMessage() ?: __('bagistoapi::app.admin.product.copy-failed');
            $isVariantError = str_contains(strtolower($msg), 'variant');

            throw new InvalidInputException(
                $isVariantError
                    ? __('bagistoapi::app.admin.product.copy-variant-not-supported')
                    : ($e->getMessage() ?: __('bagistoapi::app.admin.product.copy-failed')),
                $isVariantError ? 422 : 500,
            );
        }

        $result = new AdminCatalogProductCopy;
        $result->id = (int) ($copy->id ?? 1);
        $result->sourceId = $sourceId;
        $result->sku = $copy->sku ?? null;
        $result->type = $copy->type ?? null;
        $result->name = $copy->name ?? null;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.product.copy-success');

        return $result;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.product.no-permission'));
        }
    }

    protected function resolveSourceId(mixed $data, array $uriVariables, array $context): int
    {
        if (isset($uriVariables['sourceId'])) {
            return (int) $uriVariables['sourceId'];
        }

        if ($data instanceof AdminCatalogProductCopyInput && ! empty($data->sourceId)) {
            return (int) $data->sourceId;
        }

        $fromArgs = $context['args']['input']['sourceId']
            ?? $context['args']['sourceId']
            ?? null;
        if ($fromArgs !== null) {
            return (int) $fromArgs;
        }

        $fromRoute = request()->route('sourceId');
        if ($fromRoute !== null) {
            return (int) $fromRoute;
        }

        $fromBody = request()->input('sourceId');
        if ($fromBody !== null) {
            return (int) $fromBody;
        }

        return 0;
    }
}
