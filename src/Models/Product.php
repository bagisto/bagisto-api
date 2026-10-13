<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\ProductDetail\ProductDetailDto;
use Webkul\BagistoApi\Models\Concerns\ProductPricing;
use Webkul\BagistoApi\Models\Concerns\ProductRelations;
use Webkul\BagistoApi\Models\Concerns\ProductSystemAttributes;
use Webkul\BagistoApi\Models\Concerns\ProductWishlistCompareFlags;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\Resolver\SingleProductBagistoApiResolver;
use Webkul\BagistoApi\State\ProductDetailProvider;
use Webkul\BagistoApi\State\ProductGraphQLProvider;
use Webkul\BagistoApi\State\ProductRestProvider;
use Webkul\Product\Models\Product as BaseProduct;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get(
            output: ProductDetailDto::class,
            provider: ProductDetailProvider::class,
            normalizationContext: [
                'skip_null_values' => false,
                'allow_extra_attributes' => true,
            ],
            openapi: new Operation(
                tags: ['Product'],
                summary: 'Get a single product by ID with all relations embedded',
                description: 'Returns one product with every relation (categories, channels, images, videos, variants, bundle/grouped/downloadable options, customizable options, related/up-sell/cross-sell products) embedded inline. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The product with all relations embedded inline.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'sku' => 'COASTALBREEZEMENSHOODIE',
                                    'type' => 'simple',
                                    'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                    'urlKey' => 'coastal-breeze-mens-blue-zipper-hoodie',
                                    'status' => true,
                                    'description' => "The Coastal Breeze Men's Blue Zipper Hoodie is your reliable companion for staying warm, comfortable, and stylish.",
                                    'shortDescription' => "Stay warm and stylish with the Coastal Breeze Men's Blue Zipper Hoodie.",
                                    'price' => 100,
                                    'specialPrice' => null,
                                    'new' => true,
                                    'featured' => true,
                                    'minimumPrice' => 100,
                                    'maximumPrice' => 100,
                                    'formattedPrice' => '$100.00',
                                    'formattedSpecialPrice' => null,
                                    'formattedMinimumPrice' => '$100.00',
                                    'formattedMaximumPrice' => '$100.00',
                                    'baseImageUrl' => 'http://localhost:8000/storage/product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                    'createdAt' => '2024-04-16T21:44:20+05:30',
                                    'updatedAt' => '2026-04-08T17:23:40+05:30',
                                    'isSaleable' => true,
                                    'isInWishlist' => 0,
                                    'isInCompare' => 0,
                                    'color' => null,
                                    'size' => null,
                                    'brand' => null,
                                    'categories' => [
                                        ['id' => 24, 'slug' => 'men', 'name' => 'Men'],
                                    ],
                                    'channels' => ['/api/shop/channels/1'],
                                    'attributeFamily' => '/api/shop/attribute_families/1',
                                    'images' => [
                                        [
                                            'id' => 967,
                                            'type' => 'images',
                                            'path' => 'product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                            'productId' => 1,
                                            'position' => 1,
                                            'publicPath' => 'http://localhost:8000/storage/product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                        ],
                                    ],
                                    'videos' => [],
                                    'superAttributes' => [],
                                    'variants' => [],
                                    'bookingProducts' => [],
                                    'bundleOptions' => [],
                                    'groupedProducts' => [],
                                    'downloadableLinks' => [],
                                    'downloadableSamples' => [],
                                    'customizableOptions' => [],
                                    'relatedProducts' => [],
                                    'upSells' => [],
                                    'crossSells' => [],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Product not found.',
                    ),
                ],
            ),
        ),
        new GetCollection(
            provider: ProductRestProvider::class,
            normalizationContext: [
                'groups' => ['product:list'],
                'skip_null_values' => false,
            ],
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List products with search, filter, sort, and pagination',
                description: 'Mirrors the GraphQL products query. Any query param outside the reserved set (query, sort, order, page, per_page, locale, channel, filter) is treated as a filterable attribute — so new attributes like material, pattern, etc. work automatically without schema changes.',
                parameters: [
                    new Parameter(
                        name: 'query',
                        in: 'query',
                        description: 'Search term (matches SKU or product name).',
                        required: false,
                        schema: ['type' => 'string'],
                    ),
                    new Parameter(
                        name: 'sort',
                        in: 'query',
                        description: 'Compound sort token. One of: name-asc, name-desc, price-asc, price-desc, created_at-desc (newest first), created_at-asc (oldest first), updated_at-desc, updated_at-asc, id-asc, id-desc. May also be used with a separate `order` param (e.g. sort=name&order=desc).',
                        required: false,
                        schema: ['type' => 'string', 'example' => 'name-asc'],
                    ),
                    new Parameter(
                        name: 'order',
                        in: 'query',
                        description: 'Sort direction when `sort` is a bare key (e.g. sort=name&order=desc). Ignored if `sort` already has a -asc/-desc suffix.',
                        required: false,
                        schema: ['type' => 'string', 'enum' => ['asc', 'desc']],
                    ),
                    new Parameter(
                        name: 'page',
                        in: 'query',
                        description: 'Page number (1-based).',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 1, 'example' => 1],
                    ),
                    new Parameter(
                        name: 'per_page',
                        in: 'query',
                        description: 'Items per page. Default matches the GraphQL `first` default.',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 30, 'example' => 30],
                    ),
                    new Parameter(
                        name: 'type',
                        in: 'query',
                        description: 'Product type.',
                        required: false,
                        schema: ['type' => 'string', 'enum' => ['simple', 'configurable', 'virtual', 'downloadable', 'bundle', 'grouped', 'booking']],
                    ),
                    new Parameter(
                        name: 'category_id',
                        in: 'query',
                        description: 'Filter products by category ID.',
                        required: false,
                        schema: ['type' => 'integer', 'example' => 2],
                    ),
                    new Parameter(
                        name: 'price',
                        in: 'query',
                        description: 'Compound price range — from,to (e.g. 10,200). Alternative to price_from + price_to.',
                        required: false,
                        schema: ['type' => 'string', 'example' => '10,200'],
                    ),
                    new Parameter(
                        name: 'price_from',
                        in: 'query',
                        description: 'Minimum price (inclusive).',
                        required: false,
                        schema: ['type' => 'number'],
                    ),
                    new Parameter(
                        name: 'price_to',
                        in: 'query',
                        description: 'Maximum price (inclusive).',
                        required: false,
                        schema: ['type' => 'number'],
                    ),
                    new Parameter(
                        name: 'new',
                        in: 'query',
                        description: 'Only return products flagged as new.',
                        required: false,
                        schema: ['type' => 'boolean'],
                    ),
                    new Parameter(
                        name: 'featured',
                        in: 'query',
                        description: 'Only return products flagged as featured.',
                        required: false,
                        schema: ['type' => 'boolean'],
                    ),
                    new Parameter(
                        name: 'filter',
                        in: 'query',
                        description: 'Attribute filters as a dynamic key/value map. In Swagger UI, click "Add string item" to add a row, enter the attribute code as the key (e.g. brand, color, size, material, any filterable attribute) and the option ID as the value. Multi-select: comma-separate option IDs (e.g. 1,2,3). Generates URLs like ?filter[brand]=38&filter[color]=3. A raw JSON filter string is also accepted for GraphQL parity, e.g. {"color":{"match":"3","match_type":"PARTIAL"}}.',
                        required: false,
                        style: 'deepObject',
                        explode: true,
                        schema: [
                            'type' => 'object',
                            'additionalProperties' => ['type' => 'string'],
                            'example' => ['brand' => '38', 'color' => '3'],
                        ],
                    ),
                ],
                responses: [
                    '200' => new Response(
                        description: 'A paginated list of products matching the search, filter, and sort criteria.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'sku' => 'COASTALBREEZEMENSHOODIE',
                                        'type' => 'simple',
                                        'bookingType' => null,
                                        'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                        'urlKey' => 'coastal-breeze-mens-blue-zipper-hoodie',
                                        'status' => true,
                                        'shortDescription' => "Stay warm and stylish with the Coastal Breeze Men's Blue Zipper Hoodie.",
                                        'price' => 100,
                                        'specialPrice' => null,
                                        'new' => true,
                                        'featured' => true,
                                        'minimumPrice' => 100,
                                        'maximumPrice' => 100,
                                        'formattedPrice' => '$100.00',
                                        'formattedSpecialPrice' => null,
                                        'formattedMinimumPrice' => '$100.00',
                                        'formattedMaximumPrice' => '$100.00',
                                        'isInWishlist' => 0,
                                        'isInCompare' => 0,
                                        'baseImageUrl' => 'http://localhost:8000/storage/product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            args: [
                'id' => ['type' => 'ID'],
                'sku' => ['type' => 'String'],
                'urlKey' => ['type' => 'String'],
                'locale' => ['type' => 'String', 'description' => 'Locale code for localized data (e.g. "en", "fr")'],
                'channel' => ['type' => 'String', 'description' => 'Channel code (e.g. "default")'],
            ],
            resolver: SingleProductBagistoApiResolver::class
        ),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'Product',
    uriTemplate: '/products-collection',
    operations: [
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(
            provider: ProductGraphQLProvider::class,
            args: [
                'sortKey' => [
                    'type' => 'String',
                    'description' => 'Sort products by field (TITLE, CREATED_AT, UPDATED_AT, PRICE, etc.)',
                ],
                'reverse' => [
                    'type' => 'Boolean',
                    'description' => 'Reverse the sort order (true = descending, false = ascending)',
                ],
                'query' => [
                    'type' => 'String',
                    'description' => 'Search query to filter products by SKU or name',
                ],
                'filter' => [
                    'type' => 'String',
                    'description' => 'JSON filter object containing attribute filters (type, sku, category_id, price, color, name, etc.). Example: {"type":"configurable","sku":"ABC123"}',
                ],
                'first' => [
                    'type' => 'Int',
                    'description' => 'Limit the number of products returned',
                ],
                'after' => [
                    'type' => 'String',
                    'description' => 'Relay cursor for forward pagination',
                ],
                'before' => [
                    'type' => 'String',
                    'description' => 'Relay cursor for backward pagination',
                ],
                'last' => [
                    'type' => 'Int',
                    'description' => 'Return the last N items (used with before cursor)',
                ],
                'locale' => [
                    'type' => 'String',
                    'description' => 'Fetch data products by locale',
                ],
                'channel' => [
                    'type' => 'String',
                    'description' => 'Fetch data products by channel',
                ],
            ]
        ),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'Product',
    uriTemplate: '/products/{productId}/variants',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'variants',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['product:list']],
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'List variants of a configurable product',
                description: 'Configurable-type only. Returns the child product variants of the given configurable product. Each variant is a Product with the same card-level fields as the list endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'List of product variants',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 124,
                                        'sku' => 'SDASDAS123123-variant-1-6',
                                        'type' => 'simple',
                                        'name' => 'Clean Pink Shirt',
                                        'urlKey' => 'sdasdas123123-variant-1-6',
                                        'status' => true,
                                        'shortDescription' => 'SDASDAS123123-variant-1-6',
                                        'price' => 2040,
                                        'new' => true,
                                        'featured' => true,
                                        'minimumPrice' => 2040,
                                        'maximumPrice' => 2040,
                                        'formattedPrice' => '$2,040.00',
                                        'formattedMinimumPrice' => '$2,040.00',
                                        'formattedMaximumPrice' => '$2,040.00',
                                        'isInWishlist' => 0,
                                        'isInCompare' => 0,
                                        'baseImageUrl' => 'http://localhost:8000/storage/product/124/Ba8yoli6aFgjpiFLUcfEpuHaGzbaRCYu7wEvKR2d.webp',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: []
)]
class Product extends BaseProduct
{
    use ProductPricing;
    use ProductRelations;
    use ProductSystemAttributes;
    use ProductWishlistCompareFlags;

    public $locale;

    public $channel;

    protected $with = [
        'attribute_family',
    ];

    protected $appends = [
        'name', 'description', 'short_description', 'price', 'special_price',
        'weight', 'product_number', 'status', 'new', 'featured',
        'visible_individually', 'guest_checkout', 'manage_stock',
        'url_key', 'tax_category_id', 'special_price_from', 'special_price_to',
        'meta_title', 'meta_keywords',
        'meta_description', 'length', 'width', 'height',
        'color', 'size', 'brand', 'locale', 'channel', 'description_html',
        'minimum_price', 'maximum_price', 'regular_minimum_price', 'regular_maximum_price',
        'formatted_price', 'formatted_special_price', 'formatted_minimum_price',
        'formatted_maximum_price', 'formatted_regular_minimum_price', 'formatted_regular_maximum_price',
        'index', 'combinations', 'super_attribute_options',
        'is_in_wishlist', 'is_in_compare',
    ];

    protected static array $systemAttributes = [
        'sku' => ['id' => 1],
        'name' => ['id' => 2],
        'url_key' => ['id' => 3],
        'tax_category_id' => ['id' => 4],
        'new' => ['id' => 5],
        'featured' => ['id' => 6],
        'visible_individually' => ['id' => 7],
        'status' => ['id' => 8],
        'short_description' => ['id' => 9],
        'description' => ['id' => 10],
        'price' => ['id' => 11],
        'special_price' => ['id' => 13],
        'special_price_from' => ['id' => 14],
        'special_price_to' => ['id' => 15],
        'meta_title' => ['id' => 16],
        'meta_keywords' => ['id' => 17],
        'weight' => ['id' => 22],
        'guest_checkout' => ['id' => 26],
        'product_number' => ['id' => 27],
        'manage_stock' => ['id' => 28],
        'cost' => ['id' => 12],
        'meta_description' => ['id' => 18],
        'length' => ['id' => 19],
        'width' => ['id' => 20],
        'height' => ['id' => 21],
        'color' => ['id' => 23],
        'size' => ['id' => 24],
        'brand' => ['id' => 25],
    ];

    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['product:list'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    /** Parent */
    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getParent(): mixed
    {
        return $this->parent_id;
    }

    public function setParent(mixed $value): void
    {
        // Parent cannot be modified via API
    }

    /** Is Saleable */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['product:list'])]
    public function getIsSaleableAttribute(): bool
    {
        return parent::isSaleable();
    }

    public function isSaleable(): bool
    {
        return $this->getIsSaleableAttribute();
    }

    public function availableForSaleAttribute(): bool
    {
        return $this->status && $this->isSaleable();
    }

    /** Available for Sale */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    public function availableForSale(): bool
    {
        return $this->status && $this->isSaleable();
    }

    /**
     * Get locale attribute.
     */
    public function getLocaleAttribute(): ?string
    {
        return $this->locale;
    }

    /**
     * Get locale context.
     */
    #[ApiProperty(
        writable: true,
        readable: true
    )]
    #[Groups(['mutation'])]
    public function getLocale(): ?string
    {
        return $this->getLocaleAttribute();
    }

    /**
     * Set locale context.
     */
    public function setLocale(?string $value): void
    {
        $this->locale = $value;
    }

    /**
     * Get channel attribute.
     */
    public function getChannelAttribute(): ?string
    {
        return $this->channel;
    }

    /**
     * Get channel context.
     */
    #[ApiProperty(
        writable: true,
        readable: true
    )]
    #[Groups(['mutation'])]
    public function getChannel(): ?string
    {
        return $this->getChannelAttribute();
    }

    /**
     * Set channel context.
     */
    public function setChannel(?string $value): void
    {
        $this->channel = $value;
    }

    public function getIndexAttribute(): string
    {
        return $this->getCombinationsAttribute();
    }

    #[ApiProperty(deprecationReason: 'Use the VariantAttributeMap property instead', writable: false, readable: true, required: false)]
    public function getIndex(): ?string
    {
        $indexJson = $this->getIndexAttribute();

        return $indexJson !== '{}' ? $indexJson : null;
    }

    public function getCombinationsAttribute(): string
    {
        if ($this->type !== 'configurable') {
            return '{}';
        }

        $index = [];

        if (! $this->relationLoaded('super_attributes')) {
            $this->load('super_attributes');
        }

        if (! $this->relationLoaded('variants')) {
            $this->load([
                'variants' => function ($query) {
                    $query->with(['attribute_values.attribute']);
                },
            ]);
        }

        $superAttributeIds = $this->super_attributes->pluck('id')->toArray();
        $attributeCodeMap = $this->super_attributes->pluck('code', 'id')->toArray();

        if (empty($superAttributeIds)) {
            return '{}';
        }

        foreach ($this->variants as $variant) {
            $index[$variant->id] = $this->variantCombination($variant, $superAttributeIds, $attributeCodeMap);
        }

        return json_encode($index);
    }

    /**
     * Map a variant's super attribute values by attribute code.
     */
    private function variantCombination(mixed $variant, array $superAttributeIds, array $attributeCodeMap): array
    {
        if (! $variant->relationLoaded('attribute_values')) {
            $variant->load('attribute_values.attribute');
        }

        $combination = [];

        foreach ($variant->attribute_values as $attributeValue) {
            if (! in_array($attributeValue->attribute_id, $superAttributeIds)) {
                continue;
            }

            $attributeCode = $attributeCodeMap[$attributeValue->attribute_id] ?? null;

            if (! $attributeCode) {
                continue;
            }

            $combination[$attributeCode] = $attributeValue->value;
        }

        return $combination;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getCombinations(): ?string
    {
        $indexJson = $this->getCombinationsAttribute();

        return $indexJson !== '{}' ? $indexJson : null;
    }

    public function getSuperAttributeOptionsAttribute(): string
    {
        if ($this->type !== 'configurable') {
            return '{}';
        }

        if (! $this->relationLoaded('super_attributes')) {
            $this->load('super_attributes');
        }

        if (! $this->relationLoaded('variants')) {
            $this->load([
                'variants' => function ($query) {
                    $query->with(['attribute_values.attribute.options']);
                },
            ]);
        }

        $usedOptions = [];

        foreach ($this->variants as $variant) {
            foreach ($variant->attribute_values as $attrValue) {
                $usedOptions[$attrValue->attribute_id][] = $attrValue->value;
            }
        }

        foreach ($usedOptions as $attrId => $values) {
            $usedOptions[$attrId] = array_unique($values);
        }

        $result = [];

        foreach ($this->super_attributes as $attribute) {
            $options = [];

            foreach ($attribute->options as $option) {
                if (in_array($option->id, $usedOptions[$attribute->id] ?? [])) {
                    $options[] = [
                        'id' => $option->id,
                        'label' => $option->admin_name,
                    ];
                }
            }

            if (! empty($options)) {
                $result[] = [
                    'id' => $attribute->id,
                    'code' => $attribute->code,
                    'label' => $attribute->admin_name,
                    'options' => $options,
                ];
            }
        }

        return json_encode($result);
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getSuper_attribute_options(): ?string
    {
        $indexJson = $this->getSuperAttributeOptionsAttribute();

        return $indexJson !== '{}' ? $indexJson : null;
    }

    public function getSkuAttribute(): ?string
    {
        return $this->getSystemAttributeValue('sku');
    }

    #[ApiProperty(
        writable: true,
        readable: true
    )]
    #[Groups(['product:list', 'mutation'])]
    public function getSku(): ?string
    {
        return $this->getSkuAttribute();
    }

    public function setSku(?string $value): void
    {
        $this->setSystemAttributeValue('sku', $value);
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: true
    )]
    #[Groups(['product:list', 'mutation'])]
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $value): void
    {
        $this->type = $value;
    }

    /**
     * Cache for attribute values to avoid repeated lookups
     */
    protected array $attributeValueCache = [];

    private const AV_NULL = "\0NULL";

    private const FLAT_ATTRIBUTES = [
        'sku', 'name', 'url_key', 'short_description', 'description',
        'new', 'featured', 'status', 'visible_individually',
        'meta_title', 'meta_keywords', 'meta_description',
        'price', 'special_price', 'special_price_from', 'special_price_to',
        'weight', 'product_number',
    ];

    private bool $flatRowResolved = false;

    private mixed $flatRow = null;

    private function flatRow(): mixed
    {
        if ($this->flatRowResolved) {
            return $this->flatRow;
        }

        $this->flatRowResolved = true;

        if (! $this->relationLoaded('product_flats')) {
            return $this->flatRow = null;
        }

        $locale = $this->locale ?? app()->getLocale();
        $channel = $this->channel ?? (core()->getCurrentChannel()->code ?? 'default');

        $rows = $this->product_flats;

        $match = null;

        foreach ($rows as $row) {
            if ($row->locale === $locale && $row->channel === $channel) {
                $match = $row;

                break;
            }

            if ($match === null && $row->locale === $locale) {
                $match = $row;
            }
        }

        return $this->flatRow = $match ?? $rows->first();
    }

    private ?array $attributeValueIndex = null;

    private function attributeValueIndex(): array
    {
        if ($this->attributeValueIndex !== null) {
            return $this->attributeValueIndex;
        }

        $index = [];

        foreach ($this->attribute_values as $attributeValue) {
            $key = $attributeValue->attribute_id
                .'|'.($attributeValue->locale ?? self::AV_NULL)
                .'|'.($attributeValue->channel ?? self::AV_NULL);

            if (! array_key_exists($key, $index)) {
                $index[$key] = $attributeValue;
            }
        }

        return $this->attributeValueIndex = $index;
    }

    protected function getSystemAttributeValue(string $attributeCode): mixed
    {
        if (array_key_exists($attributeCode, $this->attributeValueCache)) {
            return $this->attributeValueCache[$attributeCode];
        }

        $tempKey = "_temp_{$attributeCode}";

        if (isset($this->attributes[$tempKey])) {
            return $this->attributeValueCache[$attributeCode] = $this->attributes[$tempKey];
        }

        if (in_array($attributeCode, self::FLAT_ATTRIBUTES, true)) {
            $flat = $this->flatRow();

            if ($flat !== null) {
                $flatValue = $flat->{$attributeCode} ?? null;

                if ($flatValue !== null) {
                    return $this->attributeValueCache[$attributeCode] = $flatValue;
                }
            }
        }

        if (! $this->relationLoaded('attribute_values')) {
            $this->load('attribute_values');
        }

        $attrConfig = static::$systemAttributes[$attributeCode] ?? null;
        if (! $attrConfig) {
            return $this->attributeValueCache[$attributeCode] = '';
        }

        $currentLocale = $this->locale ?? app()->getLocale();

        $currentChannel = $this->channel ?? (core()->getCurrentChannel()->code ?? 'default');

        $attributeValue = null;

        $localeVariants = [];
        if (! empty($currentLocale)) {
            $localeVariants[] = $currentLocale;
            if (str_contains($currentLocale, '_') || str_contains($currentLocale, '-')) {
                $localeParts = preg_split('/[_-]/', $currentLocale);
                if (! empty($localeParts[0])) {
                    $localeVariants[] = $localeParts[0];
                }
            }
        }

        $defaultLocale = core()->getCurrentChannel()->default_locale?->code;
        if ($defaultLocale && ! in_array($defaultLocale, $localeVariants)) {
            $localeVariants[] = $defaultLocale;
        }

        $localeVariants[] = null;

        $channelVariants = [$currentChannel, null];

        $index = $this->attributeValueIndex();

        foreach ($localeVariants as $localeVariant) {
            foreach ($channelVariants as $channelVariant) {
                $key = $attrConfig['id']
                    .'|'.($localeVariant ?? self::AV_NULL)
                    .'|'.($channelVariant ?? self::AV_NULL);

                if (isset($index[$key])) {
                    $attributeValue = $index[$key];

                    break 2;
                }
            }
        }

        if ($attributeValue && $attributeValue?->integer_value && in_array($attributeValue?->attribute?->type, ['select', 'multiselect', 'checkbox'])) {
            $attributeValue->setValue($attributeValue->attribute->options()->where('id', $attributeValue->value)->first()?->label);
        }

        return $this->attributeValueCache[$attributeCode] = ($attributeValue ? $attributeValue->value : '');
    }

    protected function setSystemAttributeValue(string $attributeCode, mixed $value): void
    {
        $tempKey = "_temp_{$attributeCode}";
        $this->attributes[$tempKey] = $value;
    }

    private static ?\WeakMap $priceScopeByRequest = null;

    private ?object $priceIndexMemo = null;

    private bool $priceIndexResolved = false;

    // ─── Formatted Price Accessors ──────────────────────────────────────

}
