<?php

namespace Webkul\BagistoApi\Input;

use Webkul\BagistoApi\Models\Product;

class CreateProductInput extends Product
{
    /**
     * The sku.
     */
    public ?string $sku = null;

    /**
     * The type.
     */
    public ?string $type = null;

    /**
     * The attribute family.
     */
    public ?string $attributeFamily = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The status.
     */
    public ?string $status = null;

    /**
     * The url key.
     */
    public ?string $urlKey = null;

    /**
     * The description.
     */
    public ?string $description = null;

    /**
     * The short description.
     */
    public ?string $shortDescription = null;

    /**
     * The weight.
     */
    public ?string $weight = null;

    /**
     * The product number.
     */
    public ?string $productNumber = null;

    /**
     * The price.
     */
    public ?float $price = null;

    /**
     * The special price.
     */
    public ?float $specialPrice = null;

    /**
     * The cost.
     */
    public ?float $cost = null;

    /**
     * The new.
     */
    public ?bool $new = null;

    /**
     * The featured.
     */
    public ?bool $featured = null;

    /**
     * The visible individually.
     */
    public ?bool $visibleIndividually = null;

    /**
     * The guest checkout.
     */
    public ?bool $guestCheckout = null;

    /**
     * The manage stock.
     */
    public ?bool $manageStock = null;

    /**
     * The tax category id.
     */
    public ?int $taxCategoryId = null;

    /**
     * The color.
     */
    public ?int $color = null;

    /**
     * The size.
     */
    public ?int $size = null;

    /**
     * The brand.
     */
    public ?int $brand = null;

    /**
     * The special price from.
     */
    public ?string $specialPriceFrom = null;

    /**
     * The special price to.
     */
    public ?string $specialPriceTo = null;

    /**
     * The meta title.
     */
    public ?string $metaTitle = null;

    /**
     * The meta keywords.
     */
    public ?string $metaKeywords = null;

    /**
     * The meta description.
     */
    public ?string $metaDescription = null;

    /**
     * The length.
     */
    public ?string $length = null;

    /**
     * The width.
     */
    public ?string $width = null;

    /**
     * The height.
     */
    public ?string $height = null;
}
