<?php

namespace Webkul\BagistoApi\State;

use Webkul\BagistoApi\Models\ProductImage;

class ProductImageProvider extends AbstractNestedResourceProvider
{
    protected function getModelClass(): string
    {
        return ProductImage::class;
    }

    /**
     * Gallery order, the same order the storefront draws them in.
     */
    protected function orderColumn(): ?string
    {
        return 'position';
    }
}
