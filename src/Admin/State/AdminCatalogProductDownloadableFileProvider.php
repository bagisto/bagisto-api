<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Storage;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Repositories\ProductAttributeValueRepository;

class AdminCatalogProductDownloadableFileProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    /**
     * Attribute types whose value is a stored file path.
     */
    public const FILE_ATTRIBUTE_TYPES = ['file', 'image'];

    /**
     * Create a new provider instance.
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected ProductAttributeValueRepository $productAttributeValueRepository,
    ) {}

    /**
     * Stream the stored file of a product's file or image attribute.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if (! $this->adminHasPermission($admin, 'catalog.products')) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.downloadable.no-permission'));
        }

        $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
        $attributeId = (int) ($uriVariables['attributeId'] ?? request()->route('attributeId') ?? 0);

        $path = $this->storedFilePath($productId, $attributeId);

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

    /**
     * The stored path of a product's attribute value, or null when the attribute does not hold a file.
     */
    protected function storedFilePath(int $productId, int $attributeId): ?string
    {
        $attribute = $this->attributeRepository->find($attributeId);

        if (! in_array($attribute?->type, self::FILE_ATTRIBUTE_TYPES, true)) {
            return null;
        }

        $value = $this->productAttributeValueRepository->findOneWhere([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
        ]);

        return $value?->text_value ?: null;
    }
}
