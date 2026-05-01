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
            'variants' => fn ($q) => $q->without(['variants', 'super_attributes', 'attribute_family'])
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
        ])->find($id);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        return $this->buildDto($product);
    }

    private function buildDto(Product $product): ProductDetailDto
    {
        $dto = new ProductDetailDto;

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
        $dto->color = $this->intOrNull($product->color);
        $dto->size = $this->intOrNull($product->size);
        $dto->brand = $this->intOrNull($product->brand);

        $dto->categories = $product->categories
            ->map(fn ($c) => new CategorySummaryDto(
                id: (int) $c->id,
                code: $c->code ?? null,
                name: $c->name ?? null,
                slug: $c->slug ?? null,
                url_path: $c->url_path ?? null,
            ))->values()->all();

        $dto->channels = $product->channels
            ->map(fn ($ch) => new ChannelSummaryDto(
                id: (int) $ch->id,
                code: $ch->code,
                hostname: $ch->hostname,
                currency_code: $ch->base_currency?->code,
                locale_code: $ch->default_locale?->code,
            ))->values()->all();

        if ($product->attribute_family) {
            $af = $product->attribute_family;
            $dto->attribute_family = new AttributeFamilySummaryDto(
                id: (int) $af->id,
                code: $af->code,
                name: $af->name,
            );
        }

        $dto->images = $product->images
            ->map(fn ($img) => new ProductImageDto(
                id: (int) $img->id,
                type: $img->type ?? null,
                path: $img->path,
                product_id: (int) $img->product_id,
                position: $img->position !== null ? (int) $img->position : null,
                public_path: env('API_URL').($img->url ?? ''),
            ))->values()->all();

        $dto->videos = $product->videos
            ->map(fn ($vid) => new ProductVideoDto(
                id: (int) $vid->id,
                type: $vid->type ?? null,
                path: $vid->path,
                product_id: (int) $vid->product_id,
                position: $vid->position !== null ? (int) $vid->position : null,
                public_path: env('API_URL').($vid->url ?? ''),
            ))->values()->all();

        // Super attributes + variants (configurable only)
        if ($product->type === 'configurable') {
            $superAttributeIds = $product->super_attributes->pluck('id')->toArray();
            $superAttributeCodeMap = $product->super_attributes->pluck('code', 'id')->toArray();

            // Find which option IDs are actually used by variants
            $usedOptions = [];
            foreach ($product->variants as $variant) {
                foreach ($variant->attribute_values as $av) {
                    if (in_array($av->attribute_id, $superAttributeIds)) {
                        $usedOptions[$av->attribute_id][] = $av->value;
                    }
                }
            }

            $dto->super_attributes = $product->super_attributes
                ->map(function ($attr) use ($usedOptions) {
                    $used = array_unique($usedOptions[$attr->id] ?? []);
                    $opts = [];
                    foreach ($attr->options as $opt) {
                        if (in_array($opt->id, $used)) {
                            $opts[] = new SuperAttributeOptionDto(
                                id: (int) $opt->id,
                                label: $opt->admin_name ?? $opt->label ?? null,
                                swatch_value: $opt->swatch_value ?? null,
                            );
                        }
                    }

                    return new SuperAttributeDto(
                        id: (int) $attr->id,
                        code: $attr->code,
                        label: $attr->admin_name ?? $attr->name ?? null,
                        type: $attr->type ?? null,
                        options: $opts,
                    );
                })->values()->all();

            $dto->variants = $product->variants
                ->map(function ($variant) use ($superAttributeIds, $superAttributeCodeMap) {
                    $optMap = [];
                    foreach ($variant->attribute_values as $av) {
                        if (in_array($av->attribute_id, $superAttributeIds)) {
                            $code = $superAttributeCodeMap[$av->attribute_id] ?? null;
                            if ($code) {
                                $optMap[$code] = (int) $av->value;
                            }
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
                        super_attribute_options: $optMap,
                    );
                })->values()->all();
        }

        // Booking products (booking only)
        if ($product->type === 'booking') {
            $dto->booking_products = $product->booking_products
                ->map(fn ($bp) => $this->buildBookingProduct($bp))
                ->values()->all();
        }

        // Bundle options (bundle only)
        if ($product->type === 'bundle') {
            $dto->bundle_options = $product->bundle_options
                ->map(fn ($bo) => new BundleOptionDto(
                    id: (int) $bo->id,
                    type: $bo->type,
                    is_required: (bool) $bo->is_required,
                    sort_order: $bo->sort_order !== null ? (int) $bo->sort_order : null,
                    label: $bo->label ?? null,
                    products: $bo->bundle_option_products
                        ->map(fn ($bop) => new BundleOptionProductDto(
                            id: (int) $bop->id,
                            product_id: (int) $bop->product_id,
                            qty: $bop->qty !== null ? (int) $bop->qty : null,
                            is_default: (bool) $bop->is_default,
                            sort_order: $bop->sort_order !== null ? (int) $bop->sort_order : null,
                            product: $bop->product ? $this->buildProductSummary($bop->product) : null,
                        ))->values()->all(),
                ))->values()->all();
        }

        // Grouped products (grouped only)
        if ($product->type === 'grouped') {
            $dto->grouped_products = $product->grouped_products
                ->map(fn ($gp) => new GroupedProductDto(
                    id: (int) $gp->id,
                    associated_product_id: (int) $gp->associated_product_id,
                    qty: $gp->qty !== null ? (float) $gp->qty : null,
                    sort_order: $gp->sort_order !== null ? (int) $gp->sort_order : null,
                    product: $gp->associated_product ? $this->buildProductSummary($gp->associated_product) : null,
                ))->values()->all();
        }

        // Downloadable (downloadable only)
        if ($product->type === 'downloadable') {
            $dto->downloadable_links = $product->downloadable_links
                ->map(fn ($dl) => new DownloadableLinkDto(
                    id: (int) $dl->id,
                    sort_order: $dl->sort_order !== null ? (int) $dl->sort_order : null,
                    type: $dl->type ?? null,
                    file_type: $dl->file_type ?? null,
                    url_or_path: $dl->type === 'url' ? ($dl->url ?? null) : ($dl->file ?? null),
                    sample_type: $dl->sample_type ?? null,
                    sample_file_type: $dl->sample_file_type ?? null,
                    sample_url_or_path: ($dl->sample_type ?? null) === 'url' ? ($dl->sample_url ?? null) : ($dl->sample_file ?? null),
                    downloads: $dl->downloads !== null ? (int) $dl->downloads : null,
                    price: $dl->price !== null ? (float) core()->convertPrice((float) $dl->price) : null,
                    formatted_price: $dl->price !== null ? core()->formatPrice((float) core()->convertPrice((float) $dl->price)) : null,
                    title: $dl->title ?? null,
                ))->values()->all();

            $dto->downloadable_samples = $product->downloadable_samples
                ->map(fn ($ds) => new DownloadableSampleDto(
                    id: (int) $ds->id,
                    sort_order: $ds->sort_order !== null ? (int) $ds->sort_order : null,
                    type: $ds->type ?? null,
                    file_type: $ds->file_type ?? null,
                    url_or_path: $ds->type === 'url' ? ($ds->url ?? null) : ($ds->file ?? null),
                    title: $ds->title ?? null,
                ))->values()->all();
        }

        // Customizable options (any type)
        $dto->customizable_options = $product->customizable_options
            ->map(fn ($co) => new CustomizableOptionDto(
                id: (int) $co->id,
                type: $co->type ?? null,
                is_required: (bool) $co->is_required,
                sort_order: $co->sort_order !== null ? (int) $co->sort_order : null,
                label: $co->label ?? null,
                prices: $co->customizable_option_prices
                    ->map(fn ($p) => new CustomizableOptionPriceDto(
                        id: (int) $p->id,
                        label: $p->label ?? null,
                        price: $p->price !== null ? (float) $p->price : null,
                        price_type: $p->price_type ?? null,
                        sort_order: $p->sort_order !== null ? (int) $p->sort_order : null,
                    ))->values()->all(),
            ))->values()->all();

        // Related / up-sell / cross-sell summaries
        $dto->related_products = $product->related_products
            ->map(fn ($p) => $this->buildProductSummary($p))->values()->all();
        $dto->up_sells = $product->up_sells
            ->map(fn ($p) => $this->buildProductSummary($p))->values()->all();
        $dto->cross_sells = $product->cross_sells
            ->map(fn ($p) => $this->buildProductSummary($p))->values()->all();

        return $dto;
    }

    private function buildProductSummary($p): ProductSummaryDto
    {
        return new ProductSummaryDto(
            id: (int) $p->id,
            sku: $p->sku,
            name: $p->name,
            price: $p->price !== null ? (float) $p->price : null,
            formatted_price: $p->formatted_price ?? null,
            base_image_url: method_exists($p, 'getBaseImageUrlAttribute') ? $p->getBaseImageUrlAttribute() : null,
        );
    }

    private function buildBookingProduct($bp): BookingProductDto
    {
        return BookingProductDto::fromModel($bp);
    }

    private function intOrNull($v): ?int
    {
        if ($v === null || $v === '' || $v === false) {
            return null;
        }
        if (is_numeric($v)) {
            return (int) $v;
        }

        return null;
    }
}
