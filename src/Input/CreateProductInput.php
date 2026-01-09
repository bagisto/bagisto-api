<?php

namespace Webkul\BagistoApi\Input;

use Webkul\BagistoApi\Models\Product;

class CreateProductInput extends Product
{
    public ?string $sku = null;

    public ?string $type = null;

    public ?string $attributeFamily = null;

    public ?string $name = null;

    public ?string $status = null;

    public ?string $urlKey = null;

    public ?string $description = null;

    public ?string $shortDescription = null;

    public ?string $weight = null;

    public ?string $productNumber = null;

    public ?float $price = null;

    public ?float $specialPrice = null;

    public ?float $cost = null;

    public ?bool $new = null;

    public ?bool $featured = null;

    public ?bool $visibleIndividually = null;

    public ?bool $guestCheckout = null;

    public ?bool $manageStock = null;

    public ?int $taxCategoryId = null;

    public ?int $color = null;

    public ?int $size = null;

    public ?int $brand = null;

    public ?string $specialPriceFrom = null;

    public ?string $specialPriceTo = null;

    public ?string $metaTitle = null;

    public ?string $metaKeywords = null;

    public ?string $metaDescription = null;

    public ?string $length = null;

    public ?string $width = null;

    public ?string $height = null;
}
