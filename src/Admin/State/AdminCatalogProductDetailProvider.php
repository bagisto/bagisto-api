<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProduct;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
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
    private const GRAPHQL_RELATIONS = [
        'images', 'videos', 'categories', 'inventories', 'customer_group_prices', 'translations',
        'super_attributes.options', 'variants.attribute_values', 'bundle_options.products',
        'linked_products', 'downloadable_links.translations', 'downloadable_samples.translations',
        'customizable_options.translations', 'customizable_options.prices', 'attribute_values',
        'channels', 'related_products', 'up_sells', 'cross_sells',
    ];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        if (empty($context['graphql_operation_name'])) {
            return parent::provide($operation, $uriVariables, $context);
        }

        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) ($uriVariables['id'] ?? $context['args']['id'] ?? 0);

        if ($id <= 0) {
            throw new ResourceNotFoundException(__($this->getNotFoundLangKey()));
        }

        $product = AdminCatalogProduct::with(self::GRAPHQL_RELATIONS)->find($id);

        if (! $product) {
            throw new ResourceNotFoundException(__($this->getNotFoundLangKey()));
        }

        return $product;
    }

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
    public function mapToDtoPublic(object $product): AdminCatalogProductRestDto
    {
        return $this->mapToDto($product);
    }

    public function loadEloquentForGraphQL(int $id): ?AdminCatalogProduct
    {
        return AdminCatalogProduct::with(self::GRAPHQL_RELATIONS)->find($id);
    }

    protected function findEntity(int $id): ?object
    {
        return Product::with([
            'attribute_family.attribute_groups',
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

    protected function mapToDto(object $product): AdminCatalogProductRestDto
    {
        /** @var Product $product */
        $dto = new AdminCatalogProductRestDto;

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
            'bundle' => $this->populateBundle($dto, $product),
            'grouped' => $this->populateGrouped($dto, $product),
            'downloadable' => $this->populateDownloadable($dto, $product),
            'booking' => $this->populateBooking($dto, $product),
            default => null,
        };

        $dto->customizableOptions = $this->buildCustomizableOptions($product);
        $dto->videos = $this->buildVideos($product);
        $dto->channels = $this->buildChannels($product);
        $dto->attributes = $this->buildAttributes($product);
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
            'locale' => $flat->locale,
            'name' => $flat->name,
            'description' => $flat->description,
            'shortDescription' => $flat->short_description,
            'urlKey' => $flat->url_key,
            'metaTitle' => $flat->meta_title,
            'metaDescription' => $flat->meta_description,
            'metaKeywords' => $flat->meta_keywords,
        ])->values()->all();
    }

    private function buildImages(Product $product): array
    {
        return $product->images->map(fn ($image) => [
            'id' => (int) $image->id,
            'path' => $image->path,
            'url' => Storage::url($image->path),
            'sortOrder' => (int) ($image->position ?? 0),
            'altText' => $image->alt_text,
        ])->values()->all();
    }

    private function buildCategories(Product $product): array
    {
        return $product->categories->map(function ($category) {
            $translation = $category->translations->where('locale', app()->getLocale())->first()
                ?? $category->translations->first();

            return [
                'id' => (int) $category->id,
                'name' => $translation?->name,
                'slug' => $translation?->slug,
            ];
        })->values()->all();
    }

    private function buildInventories(Product $product): array
    {
        return $product->inventories->map(fn ($inventory) => [
            'sourceId' => (int) ($inventory->inventory_source_id ?? 0),
            'sourceCode' => $inventory->inventory_source?->code,
            'qty' => (int) $inventory->qty,
        ])->values()->all();
    }

    private function buildCustomerGroupPrices(Product $product): array
    {
        return $product->customer_group_prices->map(fn ($customerGroupPrice) => [
            'id' => (int) $customerGroupPrice->id,
            'customerGroupId' => $customerGroupPrice->customer_group_id !== null ? (int) $customerGroupPrice->customer_group_id : null,
            'qty' => (int) ($customerGroupPrice->qty ?? 0),
            'valueType' => $customerGroupPrice->value_type,
            'value' => $customerGroupPrice->value !== null ? (string) $customerGroupPrice->value : null,
            'uniqueId' => $customerGroupPrice->unique_id,
        ])->values()->all();
    }

    private function populateConfigurable(AdminCatalogProductRestDto $dto, Product $product): void
    {
        $dto->superAttributes = $product->super_attributes->map(fn ($attribute) => [
            'id' => (int) $attribute->id,
            'code' => $attribute->code,
            'type' => $attribute->type,
            'adminName' => $attribute->admin_name,
            'options' => $attribute->options->map(fn ($option) => [
                'id' => (int) $option->id,
                'adminName' => $option->admin_name,
                'swatchValue' => $option->swatch_value,
                'sortOrder' => (int) ($option->sort_order ?? 0),
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
            foreach ($variant->attribute_values as $attributeValue) {
                if (! isset($superAttrMap[$attributeValue->attribute_id])) {
                    continue;
                }
                $attribute = $superAttrMap[$attributeValue->attribute_id];
                $optionId = $attributeValue->integer_value ?? $attributeValue->text_value;
                $option = $attribute->options->firstWhere('id', $optionId);
                $attrValues[$attribute->code] = $option?->admin_name ?? (string) $optionId;
            }

            $inStock = null;
            try {
                $inStock = (bool) $variant->getTypeInstance()->isSaleable();
            } catch (\Throwable) {
            }

            return [
                'id' => (int) $variant->id,
                'sku' => $variant->sku,
                'name' => $variant->product_flats->first()?->name,
                'status' => null,
                'price' => $price !== null ? (string) $price : null,
                'formattedPrice' => $price !== null ? core()->formatPrice((float) $price) : null,
                'attributeValues' => $attrValues,
                'inStock' => $inStock,
                'quantity' => (int) $variant->inventories->sum('qty'),
            ];
        })->values()->all();
    }

    private function populateBundle(AdminCatalogProductRestDto $dto, Product $product): void
    {
        $dto->bundleOptions = $product->bundle_options
            ->map(fn ($option) => $this->mapBundleOption($option))
            ->values()
            ->all();
    }

    /**
     * Map a bundle option and its products to an array.
     */
    private function mapBundleOption(mixed $option): array
    {
        $label = $this->bundleOptionLabel($option);

        $products = $option->bundle_option_products
            ->map(fn ($bundleOption) => $this->mapBundleOptionProduct($bundleOption))
            ->values()
            ->all();

        return [
            'id' => (int) $option->id,
            'label' => $label,
            'type' => $option->type,
            'position' => (int) ($option->sort_order ?? 0),
            'isRequired' => (bool) ($option->is_required ?? false),
            'products' => $products,
        ];
    }

    /**
     * Resolve the translated label of a bundle option.
     */
    private function bundleOptionLabel(mixed $option): ?string
    {
        try {
            return $option->translate(app()->getLocale())?->label
                ?? $option->translations->first()?->label;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Map a bundle option product to an array.
     */
    private function mapBundleOptionProduct(mixed $bundleOption): array
    {
        $linkedProduct = $bundleOption->product;

        return [
            'id' => (int) $bundleOption->id,
            'productId' => $linkedProduct ? (int) $linkedProduct->id : null,
            'sku' => $linkedProduct?->sku,
            'name' => $this->linkedProductName($linkedProduct),
            'qty' => (int) ($bundleOption->qty ?? 1),
            'isDefault' => (bool) ($bundleOption->is_default ?? false),
            'sortOrder' => (int) ($bundleOption->sort_order ?? 0),
        ];
    }

    /**
     * Resolve the flat name of a linked product.
     */
    private function linkedProductName(mixed $linkedProduct): ?string
    {
        try {
            return $linkedProduct?->product_flats->first()?->name;
        } catch (\Throwable) {
            return null;
        }
    }

    private function populateGrouped(AdminCatalogProductRestDto $dto, Product $product): void
    {
        $dto->linkedProducts = $product->grouped_products->map(function ($groupedProduct) {
            $associated = $groupedProduct->associated_product;
            $name = null;
            try {
                $name = $associated?->product_flats->first()?->name;
            } catch (\Throwable) {
            }

            return [
                'id' => (int) $groupedProduct->id,
                'associatedProductId' => $associated ? (int) $associated->id : null,
                'sku' => $associated?->sku,
                'name' => $name,
                'qty' => (int) ($groupedProduct->qty ?? 1),
                'sortOrder' => (int) ($groupedProduct->sort_order ?? 0),
            ];
        })->values()->all();
    }

    private function populateDownloadable(AdminCatalogProductRestDto $dto, Product $product): void
    {
        $dto->downloadableLinks = $product->downloadable_links->map(fn ($link) => [
            'id' => (int) $link->id,
            'sortOrder' => (int) ($link->sort_order ?? 0),
            'downloads' => (int) ($link->downloads ?? 0),
            'price' => $link->price !== null ? (string) $link->price : null,
            'formattedPrice' => $link->price !== null ? core()->formatPrice((float) $link->price) : null,
            'type' => $link->type,
            'file' => $link->file,
            'fileUrl' => $link->file ? Storage::url($link->file) : null,
            'sampleFile' => $link->sample_file,
            'sampleFileUrl' => $link->sample_file ? Storage::url($link->sample_file) : null,
            'sampleType' => $link->sample_type,
            'translations' => $link->translations->map(fn ($translation) => [
                'locale' => $translation->locale,
                'title' => $translation->title,
            ])->values()->all(),
        ])->values()->all();

        $dto->downloadableSamples = $product->downloadable_samples->map(fn ($sample) => [
            'id' => (int) $sample->id,
            'sortOrder' => (int) ($sample->sort_order ?? 0),
            'type' => $sample->type,
            'file' => $sample->file,
            'fileUrl' => $sample->file ? Storage::url($sample->file) : null,
            'translations' => $sample->translations->map(fn ($translation) => [
                'locale' => $translation->locale,
                'title' => $translation->title,
            ])->values()->all(),
        ])->values()->all();
    }

    private function populateBooking(AdminCatalogProductRestDto $dto, Product $product): void
    {
        $bookingProduct = $product->booking_products->first();
        if (! $bookingProduct) {
            $dto->bookingProduct = null;

            return;
        }

        $data = [
            'type' => $bookingProduct->type,
            'qty' => $bookingProduct->qty !== null ? (int) $bookingProduct->qty : null,
            'location' => $bookingProduct->location,
            'availableFrom' => $bookingProduct->available_from?->toIso8601String(),
            'availableTo' => $bookingProduct->available_to?->toIso8601String(),
            'availableEveryWeek' => (bool) ($bookingProduct->available_every_week ?? false),
            'slots' => null,
            'tickets' => null,
        ];

        $dto->bookingProduct = array_merge($data, match ($bookingProduct->type) {
            'default' => $this->defaultBookingData($bookingProduct),
            'appointment' => $this->appointmentBookingData($bookingProduct),
            'event' => $this->eventBookingData($bookingProduct),
            'rental' => $this->rentalBookingData($bookingProduct),
            'table' => $this->tableBookingData($bookingProduct),
            default => [],
        });
    }

    /**
     * Build the slot data of a default booking product.
     */
    private function defaultBookingData(mixed $bookingProduct): array
    {
        $slot = $bookingProduct->default_slot;

        if (! $slot) {
            return [];
        }

        return [
            'bookingType' => $slot->booking_type,
            'duration' => $slot->duration !== null ? (int) $slot->duration : null,
            'breakTime' => $slot->break_time !== null ? (int) $slot->break_time : null,
            'slots' => $slot->slots ?? [],
        ];
    }

    /**
     * Build the slot data of an appointment booking product.
     */
    private function appointmentBookingData(mixed $bookingProduct): array
    {
        $slot = $bookingProduct->appointment_slot;

        if (! $slot) {
            return [];
        }

        return [
            'duration' => $slot->duration !== null ? (int) $slot->duration : null,
            'breakTime' => $slot->break_time !== null ? (int) $slot->break_time : null,
            'sameSlotAllDays' => (bool) ($slot->same_slot_all_days ?? false),
            'slots' => $slot->slots ?? [],
        ];
    }

    /**
     * Build the ticket data of an event booking product.
     */
    private function eventBookingData(mixed $bookingProduct): array
    {
        return [
            'tickets' => $bookingProduct->event_tickets
                ->map(fn ($ticket) => $this->mapEventTicket($ticket))
                ->values()
                ->all(),
        ];
    }

    /**
     * Map an event ticket to an array.
     */
    private function mapEventTicket(mixed $ticket): array
    {
        $name = null;
        $description = null;

        try {
            $translation = $ticket->translate(app()->getLocale())
                ?? $ticket->translations->first();
            $name = $translation?->name;
            $description = $translation?->description;
        } catch (\Throwable) {
        }

        return [
            'id' => (int) $ticket->id,
            'price' => $ticket->price !== null ? (string) $ticket->price : null,
            'specialPrice' => $ticket->special_price !== null ? (string) $ticket->special_price : null,
            'specialPriceFrom' => $ticket->special_price_from,
            'specialPriceTo' => $ticket->special_price_to,
            'qty' => $ticket->qty !== null ? (int) $ticket->qty : null,
            'name' => $name,
            'description' => $description,
        ];
    }

    /**
     * Build the slot data of a rental booking product.
     */
    private function rentalBookingData(mixed $bookingProduct): array
    {
        $slot = $bookingProduct->rental_slot;

        if (! $slot) {
            return [];
        }

        return [
            'rentingType' => $slot->renting_type,
            'dailyPrice' => $slot->daily_price !== null ? (string) $slot->daily_price : null,
            'hourlyPrice' => $slot->hourly_price !== null ? (string) $slot->hourly_price : null,
            'sameSlotAllDays' => (bool) ($slot->same_slot_all_days ?? false),
            'slots' => $slot->slots ?? [],
        ];
    }

    /**
     * Build the slot data of a table booking product.
     */
    private function tableBookingData(mixed $bookingProduct): array
    {
        $slot = $bookingProduct->table_slot;

        if (! $slot) {
            return [];
        }

        return [
            'priceType' => $slot->price_type,
            'guestLimit' => $slot->guest_limit !== null ? (int) $slot->guest_limit : null,
            'duration' => $slot->duration !== null ? (int) $slot->duration : null,
            'breakTime' => $slot->break_time !== null ? (int) $slot->break_time : null,
            'preventSchedulingBefore' => $slot->prevent_scheduling_before !== null ? (int) $slot->prevent_scheduling_before : null,
            'sameSlotAllDays' => (bool) ($slot->same_slot_all_days ?? false),
            'slots' => $slot->slots ?? [],
        ];
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
                $translations = $transCollection->map(fn ($translation) => [
                    'locale' => $translation->locale ?? null,
                    'label' => $translation->label ?? null,
                ])->values()->all();
            } catch (\Throwable) {
            }

            $prices = [];
            try {
                $prices = $option->customizable_option_prices->map(fn ($customizableOptionPrice) => [
                    'id' => (int) $customizableOptionPrice->id,
                    'label' => $customizableOptionPrice->label,
                    'price' => $customizableOptionPrice->price !== null ? (string) $customizableOptionPrice->price : null,
                    'sortOrder' => (int) ($customizableOptionPrice->sort_order ?? 0),
                ])->values()->all();
            } catch (\Throwable) {
            }

            return [
                'id' => (int) $option->id,
                'type' => $option->type,
                'isRequired' => (bool) ($option->is_required ?? false),
                'sortOrder' => (int) ($option->sort_order ?? 0),
                'maxCharacters' => $option->max_characters !== null ? (int) $option->max_characters : null,
                'supportedFileExtensions' => $option->supported_file_extensions,
                'translations' => $translations,
                'prices' => $prices,
            ];
        })->values()->all();
    }

    private function buildVideos(Product $product): array
    {
        if (! $product->relationLoaded('videos')) {
            return [];
        }

        return $product->videos->map(fn ($video) => [
            'id' => (int) $video->id,
            'path' => $video->path,
            'url' => Storage::url($video->path),
            'sortOrder' => (int) ($video->position ?? 0),
        ])->values()->all();
    }

    private function buildChannels(Product $product): array
    {
        $assignedIds = $product->relationLoaded('channels')
            ? $product->channels->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        return collect(core()->getAllChannels())->map(fn ($channel) => [
            'id' => (int) $channel->id,
            'code' => $channel->code,
            'name' => $channel->name,
            'assigned' => in_array((int) $channel->id, $assignedIds, true),
        ])->values()->all();
    }

    /**
     * Build the attribute-family field list with the product's resolved value
     * per attribute — edit-page parity. Mirrors the core edit form exactly:
     * iterates the family's attribute groups, pulls each group's editable
     * attributes (super-attributes excluded — surfaced under superAttributes),
     * and resolves every value through the same channel/locale-aware accessor
     * the blade uses, so empty fields still appear (value null).
     */
    private function buildAttributes(Product $product): array
    {
        $family = $product->attribute_family;

        if (
            ! $family
            || ! $family->relationLoaded('attribute_groups')
        ) {
            return [];
        }

        $result = [];

        foreach ($family->attribute_groups as $group) {
            foreach ($product->getEditableAttributes($group) as $attribute) {
                $result[] = $this->mapAttributeRow($product, $attribute, $group);
            }
        }

        return $result;
    }

    /**
     * Map a product attribute and its value to an array.
     */
    private function mapAttributeRow(Product $product, mixed $attribute, mixed $group): array
    {
        $value = null;

        try {
            $value = $product->getCustomAttributeValue($attribute);
        } catch (\Throwable) {
        }

        return [
            'id' => (int) $attribute->id,
            'code' => $attribute->code,
            'adminName' => $attribute->admin_name,
            'type' => $attribute->type,
            'isRequired' => (bool) $attribute->is_required,
            'valuePerChannel' => (bool) $attribute->value_per_channel,
            'valuePerLocale' => (bool) $attribute->value_per_locale,
            'groupCode' => $group->code,
            'groupName' => $group->name,
            'value' => $value,
            'options' => $this->attributeOptions($attribute),
        ];
    }

    /**
     * Map the options of a selectable attribute to arrays.
     */
    private function attributeOptions(mixed $attribute): ?array
    {
        if (! in_array($attribute->type, ['select', 'multiselect', 'checkbox'], true)) {
            return null;
        }

        return $attribute->options->map(fn ($option) => [
            'id' => (int) $option->id,
            'adminName' => $option->admin_name,
            'swatchValue' => $option->swatch_value,
            'sortOrder' => (int) ($option->sort_order ?? 0),
        ])->values()->all();
    }

    /**
     * Build slim product reference rows for related/upSells/crossSells.
     *
     * @param  Collection|null  $products
     */
    private function buildProductRefs($products): array
    {
        if (! $products) {
            return [];
        }

        return $products->map(function ($product) {
            $name = null;
            try {
                $name = $product->product_flats->first()?->name
                    ?? $product->product_flats->where('locale', app()->getLocale())->first()?->name;
            } catch (\Throwable) {
            }

            return [
                'id' => (int) $product->id,
                'sku' => $product->sku,
                'name' => $name,
                'type' => $product->type,
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
            $id = Attribute::where('code', $code)->value('id');
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

        if (
            $flatRow
            && $flatRow->price !== null
        ) {
            return (float) $flatRow->price;
        }

        $priceAttrId = $this->resolveAttributeId($product, 'price');
        $attributeValue = $product->attribute_values->where('attribute_id', $priceAttrId)->first();

        return $attributeValue ? (float) $attributeValue->float_value : null;
    }
}
