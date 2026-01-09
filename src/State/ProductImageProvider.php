<?php

namespace Webkul\BagistoApi\State;

use Webkul\BagistoApi\Models\ProductImage;

class ProductImageProvider extends AbstractNestedResourceProvider
{
    protected function getModelClass(): string
    {
        return ProductImage::class;
    }
}
