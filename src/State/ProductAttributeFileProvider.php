<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\BagistoApi\Dto\ProductAttributeFileOutput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Product;
use Webkul\Product\Repositories\ProductAttributeValueRepository;

class ProductAttributeFileProvider implements ProviderInterface
{
    /**
     * Create a new provider instance.
     */
    public function __construct(
        protected ProductAttributeValueRepository $values,
        protected AttributeRepository $attributes
    ) {}

    /**
     * Download a stored file/image attribute for a storefront-visible product.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        $productId = (int) ($uriVariables['productId'] ?? 0);
        $attributeId = (int) ($uriVariables['attributeId'] ?? 0);

        return Storage::download($this->resolvePath($productId, $attributeId));
    }

    /**
     * Return the stored file/image attribute as a base64 payload.
     */
    public function provideForGraphQl(int $productId, int $attributeId): ProductAttributeFileOutput
    {
        $path = $this->resolvePath($productId, $attributeId);
        $content = (string) Storage::get($path);

        $output = new ProductAttributeFileOutput;
        $output->product_id = $productId;
        $output->attribute_id = $attributeId;
        $output->file_name = basename($path);
        $output->mime_type = Storage::mimeType($path) ?: 'application/octet-stream';
        $output->size = strlen($content);
        $output->content = base64_encode($content);

        return $output;
    }

    /**
     * Resolve the stored path of a file/image attribute for a storefront-visible product.
     */
    private function resolvePath(int $productId, int $attributeId): string
    {
        $product = Product::query()
            ->reachableInStorefront(request()->attributes->get('bagisto_channel'))
            ->find($productId);
        $attribute = $this->attributes->find($attributeId);

        if (
            ! $product
            || ! $attribute
            || ! in_array($attribute->type, ['file', 'image'], true)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        $value = $this->values->findOneWhere([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
        ]);
        $path = $value?->text_value;

        if (
            ! $path
            || ! Storage::exists($path)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        return $path;
    }
}
