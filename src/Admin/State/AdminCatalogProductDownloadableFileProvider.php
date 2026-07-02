<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

class AdminCatalogProductDownloadableFileProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if (! $this->adminHasPermission($admin, 'catalog.products.view')) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.downloadable.no-permission'));
        }

        $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
        $attributeId = (int) ($uriVariables['attributeId'] ?? request()->route('attributeId') ?? 0);

        $path = DB::table('product_attribute_values')
            ->where('product_id', $productId)
            ->where('attribute_id', $attributeId)
            ->value('text_value');

        if (! $path) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.downloadable.file-not-found'));
        }

        if (Storage::disk('private')->exists($path)) {
            return Storage::disk('private')->download($path);
        }

        if (Storage::exists($path)) {
            return Storage::download($path);
        }

        throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.downloadable.file-not-found'));
    }
}
