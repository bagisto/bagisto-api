<?php

namespace Webkul\BagistoApi\Admin\State;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProduct;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Product\Models\Product;

/**
 * Provider for the admin catalog product detail endpoint.
 *
 * REST  : GET /api/admin/catalog/products/{id}
 *
 * Eager-loads all relations and builds a type-aware AdminCatalogProduct DTO.
 * Type-specific blocks (superAttributes, variants, bundleOptions, linkedProducts,
 * downloadableLinks, downloadableSamples) are populated only for the matching
 * product type — all others remain null.
 *
 * Nested objects are returned as plain associative arrays to avoid API Platform
 * serialising them as IRIs (lesson from Wave 2 / Phase 1.3).
 */
class AdminCatalogProductDetailProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.product.not-found';
    }

    /**
     * Public alias of {@see findEntity()} so the Phase 5.3 create processor
     * can re-fetch a freshly-created product with the same eager-loads.
     */
    public function findEntityPublic(int $id): ?object
    {
        return $this->findEntity($id);
    }

    /**
     * Public alias of {@see mapToDto()} so the Phase 5.3 create processor can
     * reuse the type-aware detail mapping without duplicating it.
     */
    public function mapToDtoPublic(object $product): AdminCatalogProduct
    {
        return $this->mapToDto($product);
    }

    protected function findEntity(int $id): ?object
    {
        return Product::with([
            'attribute_family',
            'attribute_values',
            'categories.translations',
            'images',
            'inventories.inventory_source',
            'customer_group_prices',
            'super_attributes.options',
            'variants.attribute_values',
            'bundle_options.bundle_option_products.product',
            'grouped_products.associated_product',
            'downloadable_links.translations',
            'downloadable_samples.translations',
            'booking_products.default_slot',
            'booking_products.appointment_slot',
            'booking_products.event_tickets.translations',
            'booking_products.rental_slot',
            'booking_products.table_slot',
            'videos',
            'channels',
            'related_products',
            'up_sells',
            'cross_sells',
            'customizable_options.translations',
            'customizable_options.customizable_option_prices',
        ])->find($id);
    }

    protected function mapToDto(object $product): AdminCatalogProduct
    {
        /** @var Product $product */
        $dto = new AdminCatalogProduct;

        $dto->id = (int) $product->id;
        $dto->sku = $product->sku;
        $dto->type = $product->type;
        $dto->status = null;

        $statusValue = $product->attribute_values
            ->where('attribute_id', $this->resolveAttributeId($product, 'status'))
            ->first();
        $dto->status = $statusValue ? (int) $statusValue->boolean_value : null;

        $dto->name = $this->resolveFlatName($product);

        $price = $this->resolvePrice($product);
        $dto->price = $price !== null ? (string) $price : null;
        $dto->formattedPrice = $price !== null ? core()->formatPrice((float) $price) : null;

        $dto->quantity = (int) $product->inventories->sum('qty');

        $firstImage = $product->images->first();
        $dto->baseImageUrl = $firstImage ? Storage::url($firstImage->path) : null;
        $dto->imagesCount = $product->images->count();

        $firstCategory = $product->categories->first();
        $dto->categoryId = $firstCategory ? (int) $firstCategory->id : null;
        $dto->categoryName = $firstCategory
            ? ($firstCategory->translations->where('locale', app()->getLocale())->first()?->name
               ?? $firstCategory->translations->first()?->name)
            : null;

        $flatRow = $product->product_flats->where('locale', app()->getLocale())->first()
            ?? $product->product_flats->first();
        $dto->channel = $flatRow?->channel ?? core()->getCurrentChannel()->code;
        $dto->locale = $flatRow?->locale ?? app()->getLocale();

        $dto->attributeFamilyId = $product->attribute_family ? (int) $product->attribute_family->id : null;
        $dto->attributeFamilyName = $product->attribute_family?->name;

        $dto->urlKey = $flatRow?->url_key ?? null;

        $dto->visibleIndividually = $flatRow ? (bool) $flatRow->visible_individually : null;

        $dto->shortDescription = $flatRow?->short_description;
        $dto->description = $flatRow?->description;
        $dto->metaTitle = $flatRow?->meta_title;
        $dto->metaDescription = $flatRow?->meta_description;
        $dto->metaKeywords = $flatRow?->meta_keywords;
        $dto->weight = $flatRow ? (float) ($flatRow->weight ?? 0) : null;
        $dto->featured = $flatRow ? (bool) ($flatRow->featured ?? false) : null;
        $dto->new = $flatRow ? (bool) ($flatRow->new ?? false) : null;

        $taxAttr = $product->attribute_values
            ->where('attribute_id', $this->resolveAttributeId($product, 'tax_category_id'))
            ->first();
        $dto->taxCategoryId = $taxAttr ? (int) $taxAttr->integer_value : null;

        $manageStockAttr = $product->attribute_values
            ->where('attribute_id', $this->resolveAttributeId($product, 'manage_stock'))
            ->first();
        $dto->manageStock = $manageStockAttr ? (bool) $manageStockAttr->boolean_value : false;

        try {
            $dto->inStock = (bool) $product->getTypeInstance()->isSaleable();
        } catch (\Throwable) {
            $dto->inStock = null;
        }

        $dto->createdAt = $product->created_at?->toIso8601String();
        $dto->updatedAt = $product->updated_at?->toIso8601String();

        $dto->translations = $this->buildTranslations($product);
        $dto->images = $this->buildImages($product);
        $dto->categories = $this->buildCategories($product);
        $dto->inventories = $this->buildInventories($product);
        $dto->customerGroupPrices = $this->buildCustomerGroupPrices($product);

        match ($product->type) {
            'configurable' => $this->populateConfigurable($dto, $product),
            'bundle'       => $this->populateBundle($dto, $product),
            'grouped'      => $this->populateGrouped($dto, $product),
            'downloadable' => $this->populateDownloadable($dto, $product),
            'booking'      => $this->populateBooking($dto, $product),
            default        => null,
        };

        $dto->customizableOptions = $this->buildCustomizableOptions($product);
        $dto->videos = $this->buildVideos($product);
        $dto->channels = $this->buildChannels($product);
        $dto->relatedProducts = $this->buildProductRefs($product->related_products);
        $dto->upSells = $this->buildProductRefs($product->up_sells);
        $dto->crossSells = $this->buildProductRefs($product->cross_sells);

        return $dto;
    }

    private function buildTranslations(Product $product): array
    {
        $flats = $product->product_flats;
        if ($flats->isEmpty()) {
            return [];
        }

        return $flats->map(fn ($flat) => [
            'locale'           => $flat->locale,
            'name'             => $flat->name,
            'description'      => $flat->description,
            'shortDescription' => $flat->short_description,
            'urlKey'           => $flat->url_key,
            'metaTitle'        => $flat->meta_title,
            'metaDescription'  => $flat->meta_description,
            'metaKeywords'     => $flat->meta_keywords,
        ])->values()->all();
    }

    private function buildImages(Product $product): array
    {
        return $product->images->map(fn ($img) => [
            'id'        => (int) $img->id,
            'path'      => $img->path,
            'url'       => Storage::url($img->path),
            'sortOrder' => (int) ($img->position ?? 0),
        ])->values()->all();
    }

    private function buildCategories(Product $product): array
    {
        return $product->categories->map(function ($cat) {
            $trans = $cat->translations->where('locale', app()->getLocale())->first()
                ?? $cat->translations->first();

            return [
                'id'   => (int) $cat->id,
                'name' => $trans?->name,
                'slug' => $trans?->slug,
            ];
        })->values()->all();
    }

    private function buildInventories(Product $product): array
    {
        return $product->inventories->map(fn ($inv) => [
            'sourceId'   => (int) ($inv->inventory_source_id ?? 0),
            'sourceCode' => $inv->inventory_source?->code,
            'qty'        => (int) $inv->qty,
        ])->values()->all();
    }

    private function buildCustomerGroupPrices(Product $product): array
    {
        return $product->customer_group_prices->map(fn ($cgp) => [
            'id'              => (int) $cgp->id,
            'customerGroupId' => $cgp->customer_group_id !== null ? (int) $cgp->customer_group_id : null,
            'qty'             => (int) ($cgp->qty ?? 0),
            'valueType'       => $cgp->value_type,
            'value'           => $cgp->value !== null ? (string) $cgp->value : null,
            'uniqueId'        => $cgp->unique_id,
        ])->values()->all();
    }

    private function populateConfigurable(AdminCatalogProduct $dto, Product $product): void
    {
        $dto->superAttributes = $product->super_attributes->map(fn ($attr) => [
            'id'        => (int) $attr->id,
            'code'      => $attr->code,
            'type'      => $attr->type,
            'adminName' => $attr->admin_name,
            'options'   => $attr->options->map(fn ($opt) => [
                'id'          => (int) $opt->id,
                'adminName'   => $opt->admin_name,
                'swatchValue' => $opt->swatch_value,
                'sortOrder'   => (int) ($opt->sort_order ?? 0),
            ])->values()->all(),
        ])->values()->all();

        $superAttrMap = $product->super_attributes->keyBy('id');

        $dto->variants = $product->variants->map(function ($variant) use ($superAttrMap) {
            $price = null;
            try {
                $flatRow = $variant->product_flats->first();
                $price = $flatRow?->price ?? null;
                if ($price === null) {
                    $price = $variant->attribute_values
                        ->where('attribute_id', $this->resolveAttributeId($variant, 'price'))
                        ->first()?->float_value;
                }
            } catch (\Throwable) {
            }

            $attrValues = [];
            foreach ($variant->attribute_values as $av) {
                if (! isset($superAttrMap[$av->attribute_id])) {
                    continue;
                }
                $attr = $superAttrMap[$av->attribute_id];
                $optId = $av->integer_value ?? $av->text_value;
                $opt = $attr->options->firstWhere('id', $optId);
                $attrValues[$attr->code] = $opt?->admin_name ?? (string) $optId;
            }

            $inStock = null;
            try {
                $inStock = (bool) $variant->getTypeInstance()->isSaleable();
            } catch (\Throwable) {
            }

            return [
                'id'              => (int) $variant->id,
                'sku'             => $variant->sku,
                'name'            => $variant->product_flats->first()?->name,
                'status'          => null,
                'price'           => $price !== null ? (string) $price : null,
                'formattedPrice'  => $price !== null ? core()->formatPrice((float) $price) : null,
                'attributeValues' => $attrValues,
                'inStock'         => $inStock,
                'quantity'        => (int) $variant->inventories->sum('qty'),
            ];
        })->values()->all();
    }

    private function populateBundle(AdminCatalogProduct $dto, Product $product): void
    {
        $dto->bundleOptions = $product->bundle_options->map(function ($option) {
            $label = null;
            try {
                $label = $option->translate(app()->getLocale())?->label
                    ?? $option->translations->first()?->label;
            } catch (\Throwable) {
            }

            $products = $option->bundle_option_products->map(function ($bop) {
                $linkedProduct = $bop->product;
                $name = null;
                try {
                    $name = $linkedProduct?->product_flats->first()?->name;
                } catch (\Throwable) {
                }

                return [
                    'id'        => (int) $bop->id,
                    'productId' => $linkedProduct ? (int) $linkedProduct->id : null,
                    'sku'       => $linkedProduct?->sku,
                    'name'      => $name,
                    'qty'       => (int) ($bop->qty ?? 1),
                    'isDefault' => (bool) ($bop->is_default ?? false),
                    'sortOrder' => (int) ($bop->sort_order ?? 0),
                ];
            })->values()->all();

            return [
                'id'         => (int) $option->id,
                'label'      => $label,
                'type'       => $option->type,
                'position'   => (int) ($option->sort_order ?? 0),
                'isRequired' => (bool) ($option->is_required ?? false),
                'products'   => $products,
            ];
        })->values()->all();
    }

    private function populateGrouped(AdminCatalogProduct $dto, Product $product): void
    {
        $dto->linkedProducts = $product->grouped_products->map(function ($gp) {
            $associated = $gp->associated_product;
            $name = null;
            try {
                $name = $associated?->product_flats->first()?->name;
            } catch (\Throwable) {
            }

            return [
                'id'                  => (int) $gp->id,
                'associatedProductId' => $associated ? (int) $associated->id : null,
                'sku'                 => $associated?->sku,
                'name'                => $name,
                'qty'                 => (int) ($gp->qty ?? 1),
                'sortOrder'           => (int) ($gp->sort_order ?? 0),
            ];
        })->values()->all();
    }

    private function populateDownloadable(AdminCatalogProduct $dto, Product $product): void
    {
        $dto->downloadableLinks = $product->downloadable_links->map(fn ($link) => [
            'id'             => (int) $link->id,
            'sortOrder'      => (int) ($link->sort_order ?? 0),
            'downloads'      => (int) ($link->downloads ?? 0),
            'price'          => $link->price !== null ? (string) $link->price : null,
            'formattedPrice' => $link->price !== null ? core()->formatPrice((float) $link->price) : null,
            'type'           => $link->type,
            'file'           => $link->file,
            'fileUrl'        => $link->file ? Storage::url($link->file) : null,
            'sampleFile'     => $link->sample_file,
            'sampleFileUrl'  => $link->sample_file ? Storage::url($link->sample_file) : null,
            'sampleType'     => $link->sample_type,
            'translations'   => $link->translations->map(fn ($t) => [
                'locale' => $t->locale,
                'title'  => $t->title,
            ])->values()->all(),
        ])->values()->all();

        $dto->downloadableSamples = $product->downloadable_samples->map(fn ($sample) => [
            'id'           => (int) $sample->id,
            'sortOrder'    => (int) ($sample->sort_order ?? 0),
            'type'         => $sample->type,
            'file'         => $sample->file,
            'fileUrl'      => $sample->file ? Storage::url($sample->file) : null,
            'translations' => $sample->translations->map(fn ($t) => [
                'locale' => $t->locale,
                'title'  => $t->title,
            ])->values()->all(),
        ])->values()->all();
    }

    private function populateBooking(AdminCatalogProduct $dto, Product $product): void
    {
        $bp = $product->booking_products->first();
        if (! $bp) {
            $dto->bookingProduct = null;

            return;
        }

        $data = [
            'type'                   => $bp->type,
            'qty'                    => $bp->qty !== null ? (int) $bp->qty : null,
            'location'               => $bp->location,
            'availableFrom'          => $bp->available_from?->toIso8601String(),
            'availableTo'            => $bp->available_to?->toIso8601String(),
            'availableEveryWeek'     => (bool) ($bp->available_every_week ?? false),
            'slots'                  => null,
            'tickets'                => null,
        ];

        switch ($bp->type) {
            case 'default':
                if ($slot = $bp->default_slot) {
                    $data['bookingType'] = $slot->booking_type;
                    $data['duration'] = $slot->duration !== null ? (int) $slot->duration : null;
                    $data['breakTime'] = $slot->break_time !== null ? (int) $slot->break_time : null;
                    $data['slots'] = $slot->slots ?? [];
                }
                break;

            case 'appointment':
                if ($slot = $bp->appointment_slot) {
                    $data['duration'] = $slot->duration !== null ? (int) $slot->duration : null;
                    $data['breakTime'] = $slot->break_time !== null ? (int) $slot->break_time : null;
                    $data['sameSlotAllDays'] = (bool) ($slot->same_slot_all_days ?? false);
                    $data['slots'] = $slot->slots ?? [];
                }
                break;

            case 'event':
                $data['tickets'] = $bp->event_tickets->map(function ($ticket) {
                    $name = null;
                    $desc = null;
                    try {
                        $trans = $ticket->translate(app()->getLocale())
                            ?? $ticket->translations->first();
                        $name = $trans?->name;
                        $desc = $trans?->description;
                    } catch (\Throwable) {
                    }

                    return [
                        'id'               => (int) $ticket->id,
                        'price'            => $ticket->price !== null ? (string) $ticket->price : null,
                        'specialPrice'     => $ticket->special_price !== null ? (string) $ticket->special_price : null,
                        'specialPriceFrom' => $ticket->special_price_from,
                        'specialPriceTo'   => $ticket->special_price_to,
                        'qty'              => $ticket->qty !== null ? (int) $ticket->qty : null,
                        'name'             => $name,
                        'description'      => $desc,
                    ];
                })->values()->all();
                break;

            case 'rental':
                if ($slot = $bp->rental_slot) {
                    $data['rentingType'] = $slot->renting_type;
                    $data['dailyPrice'] = $slot->daily_price !== null ? (string) $slot->daily_price : null;
                    $data['hourlyPrice'] = $slot->hourly_price !== null ? (string) $slot->hourly_price : null;
                    $data['sameSlotAllDays'] = (bool) ($slot->same_slot_all_days ?? false);
                    $data['slots'] = $slot->slots ?? [];
                }
                break;

            case 'table':
                if ($slot = $bp->table_slot) {
                    $data['priceType'] = $slot->price_type;
                    $data['guestLimit'] = $slot->guest_limit !== null ? (int) $slot->guest_limit : null;
                    $data['duration'] = $slot->duration !== null ? (int) $slot->duration : null;
                    $data['breakTime'] = $slot->break_time !== null ? (int) $slot->break_time : null;
                    $data['preventSchedulingBefore'] = $slot->prevent_scheduling_before !== null ? (int) $slot->prevent_scheduling_before : null;
                    $data['sameSlotAllDays'] = (bool) ($slot->same_slot_all_days ?? false);
                    $data['slots'] = $slot->slots ?? [];
                }
                break;
        }

        $dto->bookingProduct = $data;
    }

    private function buildCustomizableOptions(Product $product): array
    {
        if (! $product->relationLoaded('customizable_options')) {
            return [];
        }

        return $product->customizable_options->map(function ($option) {
            $translations = [];
            try {
                $transCollection = $option->translations ?? collect();
                $translations = $transCollection->map(fn ($t) => [
                    'locale' => $t->locale ?? null,
                    'label'  => $t->label ?? null,
                ])->values()->all();
            } catch (\Throwable) {
            }

            $prices = [];
            try {
                $prices = $option->customizable_option_prices->map(fn ($p) => [
                    'id'        => (int) $p->id,
                    'label'     => $p->label,
                    'price'     => $p->price !== null ? (string) $p->price : null,
                    'sortOrder' => (int) ($p->sort_order ?? 0),
                ])->values()->all();
            } catch (\Throwable) {
            }

            return [
                'id'                        => (int) $option->id,
                'type'                      => $option->type,
                'isRequired'                => (bool) ($option->is_required ?? false),
                'sortOrder'                 => (int) ($option->sort_order ?? 0),
                'maxCharacters'             => $option->max_characters !== null ? (int) $option->max_characters : null,
                'supportedFileExtensions'   => $option->supported_file_extensions,
                'translations'              => $translations,
                'prices'                    => $prices,
            ];
        })->values()->all();
    }

    private function buildVideos(Product $product): array
    {
        if (! $product->relationLoaded('videos')) {
            return [];
        }

        return $product->videos->map(fn ($video) => [
            'id'        => (int) $video->id,
            'path'      => $video->path,
            'url'       => Storage::url($video->path),
            'sortOrder' => (int) ($video->position ?? 0),
        ])->values()->all();
    }

    private function buildChannels(Product $product): array
    {
        if (! $product->relationLoaded('channels')) {
            return [];
        }

        return $product->channels->map(fn ($ch) => [
            'id'   => (int) $ch->id,
            'code' => $ch->code,
            'name' => $ch->name,
        ])->values()->all();
    }

    /**
     * Build slim product reference rows for related/upSells/crossSells.
     *
     * @param  \Illuminate\Support\Collection|null  $products
     */
    private function buildProductRefs($products): array
    {
        if (! $products) {
            return [];
        }

        return $products->map(function ($p) {
            $name = null;
            try {
                $name = $p->product_flats->first()?->name
                    ?? $p->product_flats->where('locale', app()->getLocale())->first()?->name;
            } catch (\Throwable) {
            }

            return [
                'id'   => (int) $p->id,
                'sku'  => $p->sku,
                'name' => $name,
                'type' => $p->type,
            ];
        })->values()->all();
    }

    /**
     * Resolve the integer attribute_id for a given attribute code from the
     * product's loaded attribute_family. Falls back to 0 (no match) if the
     * family is not loaded or the attribute is not in it.
     */
    private function resolveAttributeId(Product $product, string $code): int
    {
        static $codeToIdCache = [];

        if (isset($codeToIdCache[$code])) {
            return $codeToIdCache[$code];
        }

        try {
            $id = \Webkul\Attribute\Models\Attribute::where('code', $code)->value('id');
            $codeToIdCache[$code] = (int) ($id ?? 0);
        } catch (\Throwable) {
            $codeToIdCache[$code] = 0;
        }

        return $codeToIdCache[$code];
    }

    /**
     * Resolve product name from product_flats (preferred) or attribute_values.
     */
    private function resolveFlatName(Product $product): ?string
    {
        $flatRow = $product->product_flats->where('locale', app()->getLocale())->first()
            ?? $product->product_flats->first();

        return $flatRow?->name;
    }

    /**
     * Resolve price from product_flats or attribute_values float column.
     */
    private function resolvePrice(Product $product): ?float
    {
        $flatRow = $product->product_flats->where('locale', app()->getLocale())->first()
            ?? $product->product_flats->first();

        if ($flatRow && $flatRow->price !== null) {
            return (float) $flatRow->price;
        }

        $priceAttrId = $this->resolveAttributeId($product, 'price');
        $av = $product->attribute_values->where('attribute_id', $priceAttrId)->first();

        return $av ? (float) $av->float_value : null;
    }
}
