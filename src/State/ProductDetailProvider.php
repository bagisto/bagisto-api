<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Dto\ProductDetail\AttributeFamilySummaryDto;
use Webkul\BagistoApi\Dto\ProductDetail\BookingProductDto;
use Webkul\BagistoApi\Dto\ProductDetail\BundleOptionDto;
use Webkul\BagistoApi\Dto\ProductDetail\BundleOptionProductDto;
use Webkul\BagistoApi\Dto\ProductDetail\CategorySummaryDto;
use Webkul\BagistoApi\Dto\ProductDetail\ChannelSummaryDto;
use Webkul\BagistoApi\Dto\ProductDetail\CustomizableOptionDto;
use Webkul\BagistoApi\Dto\ProductDetail\CustomizableOptionPriceDto;
use Webkul\BagistoApi\Dto\ProductDetail\DownloadableLinkDto;
use Webkul\BagistoApi\Dto\ProductDetail\DownloadableSampleDto;
use Webkul\BagistoApi\Dto\ProductDetail\GroupedProductDto;
use Webkul\BagistoApi\Dto\ProductDetail\ProductDetailDto;
use Webkul\BagistoApi\Dto\ProductDetail\ProductImageDto;
use Webkul\BagistoApi\Dto\ProductDetail\ProductSummaryDto;
use Webkul\BagistoApi\Dto\ProductDetail\ProductVideoDto;
use Webkul\BagistoApi\Dto\ProductDetail\SuperAttributeDto;
use Webkul\BagistoApi\Dto\ProductDetail\SuperAttributeOptionDto;
use Webkul\BagistoApi\Dto\ProductDetail\VariantSummaryDto;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Product;

class ProductDetailProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ProductDetailDto
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        $product = Product::with([
            'attribute_family',
            'attribute_values',
            'categories',
            'channels',
            'images',
            'videos',
            'super_attributes.options',
            'variants' => fn ($query) => $query->without(['variants', 'super_attributes', 'attribute_family'])
                ->with(['images', 'attribute_values']),
            'booking_products',
            'bundle_options.bundle_option_products.product.images',
            'grouped_products.associated_product.images',
            'downloadable_links',
            'downloadable_samples',
            'customizable_options.customizable_option_prices',
            'related_products.images',
            'up_sells.images',
            'cross_sells.images',
            'price_indices',
        ])->visibleInStorefront(request()->attributes->get('bagisto_channel'))->find($id);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        return $this->buildDto($product);
    }

    private function buildDto(Product $product): ProductDetailDto
    {
        $dto = new ProductDetailDto;

        $this->fillBasics($dto, $product);
        $this->fillTaxonomy($dto, $product);
        $this->fillMedia($dto, $product);
        $this->fillTypeSpecific($dto, $product);
        $this->fillCustomizableOptions($dto, $product);
        $this->fillRelatedProducts($dto, $product);

        return $dto;
    }

    /**
     * Scalar fields, prices, wishlist and compare flags.
     */
    private function fillBasics(ProductDetailDto $dto, Product $product): void
    {
        $dto->id = (int) $product->id;
        $dto->sku = $product->sku;
        $dto->type = $product->type;
        $dto->name = $product->name;
        $dto->url_key = $product->url_key;
        $dto->status = (bool) $product->status;
        $dto->description = $product->description;
        $dto->short_description = $product->short_description;
        $dto->price = $product->price !== null ? (float) $product->price : null;
        $dto->special_price = $product->special_price !== null ? (float) $product->special_price : null;
        $dto->new = (bool) $product->new;
        $dto->featured = (bool) $product->featured;
        $dto->minimum_price = (float) $product->minimum_price;
        $dto->maximum_price = (float) $product->maximum_price;
        $dto->formatted_price = $product->formatted_price;
        $dto->formatted_special_price = $product->formatted_special_price;
        $dto->formatted_minimum_price = $product->formatted_minimum_price;
        $dto->formatted_maximum_price = $product->formatted_maximum_price;
        $dto->base_image_url = $product->getBaseImageUrlAttribute();
        $dto->created_at = $product->created_at?->toAtomString();
        $dto->updated_at = $product->updated_at?->toAtomString();
        $dto->is_saleable = $product->isSaleable();
        $dto->is_in_wishlist = app(ProductRelationFlagResolver::class)->isInWishlist((int) $product->id) ? 1 : 0;
        $dto->is_in_compare = app(ProductRelationFlagResolver::class)->isInCompare((int) $product->id) ? 1 : 0;
        $dto->attributes = $this->buildAttributes($product);
        $dto->color = $this->intOrNull($product->color);
        $dto->size = $this->intOrNull($product->size);
        $dto->brand = $this->intOrNull($product->brand);
    }

    /**
     * Categories, channels and attribute family.
     */
    private function fillTaxonomy(ProductDetailDto $dto, Product $product): void
    {
        $dto->categories = $product->categories
            ->map(fn ($category) => new CategorySummaryDto(
                id: (int) $category->id,
                code: $category->code ?? null,
                name: $category->name ?? null,
                slug: $category->slug ?? null,
                url_path: $category->url_path ?? null,
            ))->values()->all();

        $dto->channels = $product->channels
            ->map(fn ($channel) => new ChannelSummaryDto(
                id: (int) $channel->id,
                code: $channel->code,
                hostname: $channel->hostname,
                currency_code: $channel->base_currency?->code,
                locale_code: $channel->default_locale?->code,
            ))->values()->all();

        if ($product->attribute_family) {
            $attributeFamily = $product->attribute_family;
            $dto->attribute_family = new AttributeFamilySummaryDto(
                id: (int) $attributeFamily->id,
                code: $attributeFamily->code,
                name: $attributeFamily->name,
            );
        }
    }

    /**
     * Images and videos.
     */
    private function fillMedia(ProductDetailDto $dto, Product $product): void
    {
        $dto->images = $product->images
            ->map(fn ($image) => new ProductImageDto(
                id: (int) $image->id,
                type: $image->type ?? null,
                path: $image->path,
                product_id: (int) $image->product_id,
                position: $image->position !== null ? (int) $image->position : null,
                public_path: $image->url ?? '',
            ))->values()->all();

        $dto->videos = $product->videos
            ->map(fn ($video) => new ProductVideoDto(
                id: (int) $video->id,
                type: $video->type ?? null,
                path: $video->path,
                product_id: (int) $video->product_id,
                position: $video->position !== null ? (int) $video->position : null,
                public_path: $video->url ?? '',
            ))->values()->all();
    }

    /**
     * Fields that only exist for one product type.
     */
    private function fillTypeSpecific(ProductDetailDto $dto, Product $product): void
    {
        match ($product->type) {
            'configurable' => $this->fillConfigurable($dto, $product),
            'booking' => $this->fillBooking($dto, $product),
            'bundle' => $this->fillBundle($dto, $product),
            'grouped' => $this->fillGrouped($dto, $product),
            'downloadable' => $this->fillDownloadable($dto, $product),
            default => null,
        };
    }

    /**
     * Super attributes limited to the options variants use, and the variants themselves.
     */
    private function fillConfigurable(ProductDetailDto $dto, Product $product): void
    {
        $superAttributeIds = $product->super_attributes->pluck('id')->toArray();
        $superAttributeCodeMap = $product->super_attributes->pluck('code', 'id')->toArray();

        $usedOptions = $this->usedSuperAttributeOptions($product, $superAttributeIds);

        $dto->super_attributes = $product->super_attributes
            ->map(fn ($attribute) => $this->buildSuperAttribute($attribute, $usedOptions))
            ->values()->all();

        $dto->variants = $product->variants
            ->map(fn ($variant) => $this->buildVariantSummary($variant, $superAttributeIds, $superAttributeCodeMap))
            ->values()->all();
    }

    /**
     * Option values per super attribute id that at least one variant uses.
     *
     * @return array<int, array<int, mixed>>
     */
    private function usedSuperAttributeOptions(Product $product, array $superAttributeIds): array
    {
        $usedOptions = [];

        foreach ($product->variants as $variant) {
            foreach ($variant->attribute_values as $attributeValue) {
                if (in_array($attributeValue->attribute_id, $superAttributeIds)) {
                    $usedOptions[$attributeValue->attribute_id][] = $attributeValue->value;
                }
            }
        }

        return $usedOptions;
    }

    private function buildSuperAttribute($attribute, array $usedOptions): SuperAttributeDto
    {
        $used = array_unique($usedOptions[$attribute->id] ?? []);
        $options = [];

        foreach ($attribute->options as $option) {
            if (in_array($option->id, $used)) {
                $options[] = new SuperAttributeOptionDto(
                    id: (int) $option->id,
                    label: $option->admin_name ?? $option->label ?? null,
                    swatch_value: $option->swatch_value ?? null,
                );
            }
        }

        return new SuperAttributeDto(
            id: (int) $attribute->id,
            code: $attribute->code,
            label: $attribute->admin_name ?? $attribute->name ?? null,
            type: $attribute->type ?? null,
            options: $options,
        );
    }

    private function buildVariantSummary($variant, array $superAttributeIds, array $superAttributeCodeMap): VariantSummaryDto
    {
        $optionMap = [];

        foreach ($variant->attribute_values as $variantAttributeValue) {
            if (! in_array($variantAttributeValue->attribute_id, $superAttributeIds)) {
                continue;
            }

            $code = $superAttributeCodeMap[$variantAttributeValue->attribute_id] ?? null;

            if ($code) {
                $optionMap[$code] = (int) $variantAttributeValue->value;
            }
        }

        return new VariantSummaryDto(
            id: (int) $variant->id,
            sku: $variant->sku,
            name: $variant->name,
            price: $variant->price !== null ? (float) $variant->price : null,
            formatted_price: $variant->formatted_price,
            is_saleable: method_exists($variant, 'isSaleable') ? $variant->isSaleable() : null,
            base_image_url: $variant->getBaseImageUrlAttribute(),
            super_attribute_options: $optionMap,
        );
    }

    private function fillBooking(ProductDetailDto $dto, Product $product): void
    {
        $dto->booking_products = $product->booking_products
            ->map(fn ($bookingProduct) => $this->buildBookingProduct($bookingProduct))
            ->values()->all();
    }

    private function fillBundle(ProductDetailDto $dto, Product $product): void
    {
        $dto->bundle_options = $product->bundle_options
            ->map(fn ($bundleOption) => new BundleOptionDto(
                id: (int) $bundleOption->id,
                type: $bundleOption->type,
                is_required: (bool) $bundleOption->is_required,
                sort_order: $bundleOption->sort_order !== null ? (int) $bundleOption->sort_order : null,
                label: $bundleOption->label ?? null,
                products: $bundleOption->bundle_option_products
                    ->map(fn ($bundleOptionProduct) => new BundleOptionProductDto(
                        id: (int) $bundleOptionProduct->id,
                        product_id: (int) $bundleOptionProduct->product_id,
                        qty: $bundleOptionProduct->qty !== null ? (int) $bundleOptionProduct->qty : null,
                        is_default: (bool) $bundleOptionProduct->is_default,
                        sort_order: $bundleOptionProduct->sort_order !== null ? (int) $bundleOptionProduct->sort_order : null,
                        product: $bundleOptionProduct->product ? $this->buildProductSummary($bundleOptionProduct->product) : null,
                    ))->values()->all(),
            ))->values()->all();
    }

    private function fillGrouped(ProductDetailDto $dto, Product $product): void
    {
        $dto->grouped_products = $product->grouped_products
            ->map(fn ($groupedProduct) => new GroupedProductDto(
                id: (int) $groupedProduct->id,
                associated_product_id: (int) $groupedProduct->associated_product_id,
                qty: $groupedProduct->qty !== null ? (float) $groupedProduct->qty : null,
                sort_order: $groupedProduct->sort_order !== null ? (int) $groupedProduct->sort_order : null,
                product: $groupedProduct->associated_product ? $this->buildProductSummary($groupedProduct->associated_product) : null,
            ))->values()->all();
    }

    private function fillDownloadable(ProductDetailDto $dto, Product $product): void
    {
        $dto->downloadable_links = $product->downloadable_links
            ->map(fn ($downloadableLink) => new DownloadableLinkDto(
                id: (int) $downloadableLink->id,
                sort_order: $downloadableLink->sort_order !== null ? (int) $downloadableLink->sort_order : null,
                type: $downloadableLink->type ?? null,
                file_type: $downloadableLink->file_type ?? null,
                url_or_path: null,
                sample_type: $downloadableLink->sample_type ?? null,
                sample_file_type: $downloadableLink->sample_file_type ?? null,
                sample_url_or_path: ($downloadableLink->sample_type ?? null) === 'url' ? ($downloadableLink->sample_url ?? null) : ($downloadableLink->sample_file ?? null),
                downloads: $downloadableLink->downloads !== null ? (int) $downloadableLink->downloads : null,
                price: $downloadableLink->price,
                formatted_price: $downloadableLink->price !== null ? core()->formatPrice($downloadableLink->price) : null,
                title: $downloadableLink->title ?? null,
            ))->values()->all();

        $dto->downloadable_samples = $product->downloadable_samples
            ->map(fn ($downloadableSample) => new DownloadableSampleDto(
                id: (int) $downloadableSample->id,
                sort_order: $downloadableSample->sort_order !== null ? (int) $downloadableSample->sort_order : null,
                type: $downloadableSample->type ?? null,
                file_type: $downloadableSample->file_type ?? null,
                url_or_path: $downloadableSample->type === 'url' ? ($downloadableSample->url ?? null) : ($downloadableSample->file ?? null),
                title: $downloadableSample->title ?? null,
            ))->values()->all();
    }

    private function fillCustomizableOptions(ProductDetailDto $dto, Product $product): void
    {
        $dto->customizable_options = $product->customizable_options
            ->map(fn ($customizableOption) => new CustomizableOptionDto(
                id: (int) $customizableOption->id,
                type: $customizableOption->type ?? null,
                is_required: (bool) $customizableOption->is_required,
                sort_order: $customizableOption->sort_order !== null ? (int) $customizableOption->sort_order : null,
                label: $customizableOption->label ?? null,
                prices: $customizableOption->customizable_option_prices
                    ->map(fn ($customizableOptionPrice) => new CustomizableOptionPriceDto(
                        id: (int) $customizableOptionPrice->id,
                        label: $customizableOptionPrice->label ?? null,
                        price: $customizableOptionPrice->price !== null ? (float) $customizableOptionPrice->price : null,
                        price_type: $customizableOptionPrice->price_type ?? null,
                        sort_order: $customizableOptionPrice->sort_order !== null ? (int) $customizableOptionPrice->sort_order : null,
                    ))->values()->all(),
            ))->values()->all();
    }

    private function fillRelatedProducts(ProductDetailDto $dto, Product $product): void
    {
        $dto->related_products = $product->related_products
            ->map(fn ($relatedProduct) => $this->buildProductSummary($relatedProduct))->values()->all();
        $dto->up_sells = $product->up_sells
            ->map(fn ($upSell) => $this->buildProductSummary($upSell))->values()->all();
        $dto->cross_sells = $product->cross_sells
            ->map(fn ($crossSell) => $this->buildProductSummary($crossSell))->values()->all();
    }

    private function buildProductSummary($product): ProductSummaryDto
    {
        return new ProductSummaryDto(
            id: (int) $product->id,
            sku: $product->sku,
            name: $product->name,
            price: $product->price !== null ? (float) $product->price : null,
            formatted_price: $product->formatted_price ?? null,
            base_image_url: method_exists($product, 'getBaseImageUrlAttribute') ? $product->getBaseImageUrlAttribute() : null,
        );
    }

    private function buildBookingProduct($bookingProduct): BookingProductDto
    {
        return BookingProductDto::fromModel($bookingProduct);
    }

    private function intOrNull($value): ?int
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * Dynamic storefront attribute list. Iterates the product's attribute family
     * and emits every attribute flagged "Visible on Front" (is_visible_on_front),
     * channel/locale-aware. Because it is driven by the attribute definition, any
     * attribute — allow_rma, material, or a merchant-added custom one — appears
     * automatically once its "Visible on Front" flag is on, with no package change.
     * Internal fields (cost, sku, price, ...) stay hidden (is_visible_on_front = 0).
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildAttributes(Product $product): array
    {
        $family = $product->attribute_family;

        if (! $family) {
            return [];
        }

        $family->loadMissing('attribute_groups.custom_attributes.options');

        $result = [];

        foreach ($family->attribute_groups as $group) {
            foreach ($product->getEditableAttributes($group) as $attribute) {
                if (! $attribute->is_visible_on_front) {
                    continue;
                }

                $result[] = [
                    'code' => $attribute->code,
                    'label' => $attribute->name ?: $attribute->admin_name,
                    'type' => $attribute->type,
                    'value' => $this->resolveAttributeValue($attribute, $this->safeAttributeValue($product, $attribute)),
                ];
            }
        }

        return $result;
    }

    /**
     * Read a custom attribute value, or null when the lookup fails.
     */
    private function safeAttributeValue(Product $product, $attribute): mixed
    {
        try {
            return $product->getCustomAttributeValue($attribute);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve a raw EAV value to a display value: option label(s) for
     * select/checkbox/multiselect, 0/1 for boolean, raw scalar otherwise.
     */
    private function resolveAttributeValue($attribute, $raw): mixed
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (in_array($attribute->type, ['select', 'checkbox'], true)) {
            return $attribute->options->firstWhere('id', (int) $raw)?->admin_name ?? $raw;
        }

        if ($attribute->type === 'multiselect') {
            $ids = array_map('intval', is_array($raw) ? $raw : explode(',', (string) $raw));

            return $attribute->options->whereIn('id', $ids)->pluck('admin_name')->values()->all();
        }

        if ($attribute->type === 'boolean') {
            return (int) (bool) $raw;
        }

        return $raw;
    }
}
