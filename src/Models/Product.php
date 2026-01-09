<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Http\Requests\Admin\ProductFormRequest;
use Webkul\BagistoApi\Resolver\SingleProductBagistoApiResolver;
use Webkul\BagistoApi\State\ProductBagistoApiProvider;
use Webkul\BagistoApi\State\ProductProcessor;
use Webkul\Product\Models\Product as BaseProduct;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get,
        new Post(
            security: "is_granted('CREATE_PRODUCT')",
            processor: ProductProcessor::class,
            // rules: ProductFormRequest::class,
            openapi: new Model\Operation(
                summary: 'Store the product',
                description: 'Product creation endpoint',
                tags: ['Product'],
                parameters: [],
                requestBody: new Model\RequestBody(
                    description: 'Product creation payload',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'type' => [
                                        'type'    => 'string',
                                        'example' => 'simple',
                                    ],
                                    'attribute_family_id' => [
                                        'type'    => 'integer',
                                        'example' => 1,
                                    ],
                                    'sku' => [
                                        'type'    => 'string',
                                        'example' => 'furniture',
                                    ],
                                    'super_attributes' => [
                                        'type'    => 'object',
                                        'example' => [
                                            'color' => [1],
                                            'size'  => [6],
                                        ],
                                    ],
                                ],
                            ],
                            'examples' => [
                                'simple_product' => [
                                    'summary'     => 'Simple Product',
                                    'description' => 'Create a standard simple product',
                                    'value'       => [
                                        'type'                => 'simple',
                                        'attribute_family_id' => 1,
                                        'sku'                 => 'furniture',
                                    ],
                                ],
                                'configurable_product' => [
                                    'summary'     => 'Configurable Product',
                                    'description' => 'Create a configurable product with variations',
                                    'value'       => [
                                        'type'                => 'configurable',
                                        'attribute_family_id' => 1,
                                        'sku'                 => 'furniture',
                                        'super_attributes'    => [
                                            'color' => [1],
                                            'size'  => [6],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Put(
            security: "is_granted('EDIT_PRODUCT')",
            processor: ProductProcessor::class,
            openapi: new Model\Operation(
                summary: 'Update the product',
                description: 'Product update endpoint',
                tags: ['Product'],
                parameters: [],
                requestBody: new Model\RequestBody(
                    description: 'Product update payload',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'type' => [
                                        'type'    => 'string',
                                        'example' => 'simple',
                                    ],
                                    'attribute_family_id' => [
                                        'type'    => 'integer',
                                        'example' => 1,
                                    ],
                                    'sku' => [
                                        'type'    => 'string',
                                        'example' => 'furniture',
                                    ],
                                    'super_attributes' => [
                                        'type'    => 'object',
                                        'example' => [
                                            'color' => [1],
                                            'size'  => [6],
                                        ],
                                    ],
                                ],
                            ],
                            'examples' => [
                                'simple_product' => [
                                    'summary'     => 'Simple Product',
                                    'description' => 'Create a standard simple product',
                                    'value'       => [
                                        'channel'              => 'default',
                                        'locale'               => 'en',
                                        'sku'                  => 'furniture',
                                        'product_number'       => 'ssf-001',
                                        'name'                 => 'Sofa Set',
                                        'url_key'              => 'sofa-set-furniture',
                                        'tax_category_id'      => null,
                                        'new'                  => 1,
                                        'featured'             => 1,
                                        'manage_stock'         => 1,
                                        'visible_individually' => 1,
                                        'guest_checkout'       => 0,
                                        'status'               => 1,
                                        'color'                => 3,
                                        'size'                 => 9,
                                        'brand'                => 17,
                                        'short_description'    => 'What is Lorem Ipsum?',
                                        'description'          => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                                        'meta_title'           => 'Premium sofa sets',
                                        'meta_description'     => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                                        'meta_keywords'        => 'Sofa set',
                                        'price'                => 10.5,
                                        'cost'                 => 0,
                                        'special_price'        => 8.3,
                                        'special_price_from'   => '2023-05-30',
                                        'special_price_to'     => '2025-05-22',
                                        'length'               => 0,
                                        'width'                => 0,
                                        'height'               => 0,
                                        'weight'               => 1,
                                        'inventories'          => [
                                            '1' => 500,
                                        ],
                                        'images' => [
                                            'files'    => [],
                                            'position' => [1],
                                        ],
                                        'videos' => [
                                            'files'    => [],
                                            'position' => [1],
                                        ],
                                        'categories'       => [1],
                                        'channels'         => [1],
                                        'up_sell'          => [1],
                                        'cross_sell'       => [1],
                                        'related_products' => [1],
                                    ],
                                ],
                                'configurable_product' => [
                                    'summary'     => 'Configurable Product',
                                    'description' => 'Update a configurable product with variations',
                                    'value'       => [
                                        'channel'               => 'default',
                                        'locale'                => 'en',
                                        'sku'                   => 'skipping-rope',
                                        'product_number'        => 'sr-001',
                                        'name'                  => 'Skipping Rope',
                                        'url_key'               => 'skipping-rope',
                                        'tax_category_id'       => null,
                                        'new'                   => 1,
                                        'featured'              => 1,
                                        'visible_individually'  => 1,
                                        'guest_checkout'        => 0,
                                        'status'                => 1,
                                        'brand'                 => 17,
                                        'short_description'     => 'What is Lorem Ipsum?',
                                        'description'           => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                                        'meta_title'            => 'Premium sofa sets',
                                        'meta_description'      => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                                        'meta_keywords'         => 'Sofa set',
                                        'price'                 => 0,
                                        'customer_group_prices' => [
                                            'customer_group_price_0' => [
                                                'customer_group_id' => 1,
                                                'qty'               => 2,
                                                'value_type'        => 'fixed',
                                                'value'             => 3.2,
                                            ],
                                        ],
                                        'categories' => [
                                            1,
                                            2,
                                        ],
                                        'channels' => [
                                            1,
                                            3,
                                            4,
                                        ],
                                        'variants' => [
                                            '28' => [
                                                'sku'         => 'skipping-rope-variant-1-6',
                                                'name'        => 'Red-S',
                                                'color'       => 1,
                                                'size'        => 6,
                                                'price'       => 10.5,
                                                'weight'      => 1.2,
                                                'status'      => 1,
                                                'inventories' => [
                                                    '1' => 500,
                                                ],
                                                'images[]' => [
                                                    'string',
                                                ],
                                            ],
                                            '29' => [
                                                'sku'         => 'skipping-rope-variant-1-7',
                                                'name'        => 'Red-M',
                                                'color'       => 1,
                                                'size'        => 7,
                                                'price'       => 15,
                                                'weight'      => 1,
                                                'status'      => 1,
                                                'inventories' => [
                                                    '1' => 500,
                                                ],
                                                'images[files]' => [
                                                    'string',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'downloadable_product' => [
                                    'summary'     => 'Downloadable Product',
                                    'description' => 'Update a downloadable product with links and samples',
                                    'value'       => [
                                        'channel'               => 'default',
                                        'locale'                => 'en',
                                        'sku'                   => 'skipping-rope',
                                        'product_number'        => 'sr-001',
                                        'name'                  => 'Skipping Rope',
                                        'url_key'               => 'skipping-rope',
                                        'tax_category_id'       => null,
                                        'new'                   => 1,
                                        'featured'              => 1,
                                        'visible_individually'  => 1,
                                        'guest_checkout'        => 0,
                                        'status'                => 1,
                                        'brand'                 => 17,
                                        'short_description'     => 'What is Lorem Ipsum?',
                                        'description'           => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                                        'meta_title'            => 'Premium sofa sets',
                                        'meta_description'      => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                                        'meta_keywords'         => 'Sofa set',
                                        'price'                 => 0,
                                        'customer_group_prices' => [
                                            'customer_group_price_0' => [
                                                'customer_group_id' => 1,
                                                'qty'               => 2,
                                                'value_type'        => 'fixed',
                                                'value'             => 3.2,
                                            ],
                                        ],
                                        'categories' => [
                                            1,
                                            2,
                                        ],
                                        'channels' => [
                                            1,
                                            3,
                                            4,
                                        ],
                                        'downloadable_links' => [
                                            'link_0' => [
                                                'en' => [
                                                    'title' => 'Link 1',
                                                ],
                                                'price'       => 5,
                                                'type'        => 'url',
                                                'url'         => 'https://cdn.pixabay.com/photo/2016/03/26/13/08/conceptual-1280533_1280.jpg',
                                                'sample_type' => 'url',
                                                'sample_url'  => 'https://cdn.pixabay.com/photo/2016/11/22/19/11/brick-wall-1850095_1280.jpg',
                                                'downloads'   => 10,
                                                'sort_order'  => 1,
                                            ],
                                            'link_1' => [
                                                'en' => [
                                                    'title' => 'Link 2',
                                                ],
                                                'price'       => 10,
                                                'type'        => 'url',
                                                'url'         => 'https://cdn.pixabay.com/photo/2016/03/26/13/08/conceptual-1280533_1280.jpg',
                                                'sample_type' => 'url',
                                                'sample_url'  => 'https://cdn.pixabay.com/photo/2016/11/22/19/11/brick-wall-1850095_1280.jpg',
                                                'downloads'   => 20,
                                                'sort_order'  => 2,
                                            ],
                                        ],
                                        'downloadable_samples' => [
                                            'sample_0' => [
                                                'en' => [
                                                    'title' => 'Sample 1',
                                                ],
                                                'type'       => 'url',
                                                'url'        => 'https://cdn.pixabay.com/photo/2017/10/04/14/50/staging-2816464_1280.jpg',
                                                'sort_order' => 1,
                                            ],
                                            'sample_1' => [
                                                'en' => [
                                                    'title' => 'Sample 2',
                                                ],
                                                'type'       => 'url',
                                                'url'        => 'https://cdn.pixabay.com/photo/2015/12/05/23/38/nursery-1078923_1280.jpg',
                                                'sort_order' => 2,
                                            ],
                                        ],
                                    ],
                                ],
                                'grouped_product' => [
                                    'summary'     => 'Group Product',
                                    'description' => 'Update a grouped product along with its associated products',
                                    'value'       => [
                                        'channel'               => 'default',
                                        'locale'                => 'en',
                                        'sku'                   => 'skipping-rope',
                                        'product_number'        => 'sr-001',
                                        'name'                  => 'Skipping Rope',
                                        'url_key'               => 'skipping-rope',
                                        'tax_category_id'       => null,
                                        'new'                   => 1,
                                        'featured'              => 1,
                                        'visible_individually'  => 1,
                                        'guest_checkout'        => 0,
                                        'status'                => 1,
                                        'brand'                 => 17,
                                        'short_description'     => 'What is Lorem Ipsum?',
                                        'description'           => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                                        'meta_title'            => 'Premium sofa sets',
                                        'meta_description'      => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                                        'meta_keywords'         => 'Sofa set',
                                        'price'                 => 0,
                                        'customer_group_prices' => [
                                            'customer_group_price_0' => [
                                                'customer_group_id' => 1,
                                                'qty'               => 2,
                                                'value_type'        => 'fixed',
                                                'value'             => 3.2,
                                            ],
                                        ],
                                        'categories' => [
                                            1,
                                            2,
                                        ],
                                        'channels' => [
                                            1,
                                            3,
                                            4,
                                        ],
                                        'links' => [
                                            'link_0' => [
                                                'associated_product_id' => 1,
                                                'qty'                   => 2,
                                                'sort_order'            => 1,
                                            ],
                                            'link_1' => [
                                                'associated_product_id' => 2,
                                                'qty'                   => 3,
                                                'sort_order'            => 2,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            processor: \Webkul\BagistoApi\State\ProductProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
        new Mutation(
            name: 'update',
            processor: \Webkul\BagistoApi\State\ProductProcessor::class,
        ),
        new Query(
            args: [
                'id'     => ['type' => 'ID'],
                'sku'    => ['type' => 'String'],
                'urlKey' => ['type' => 'String'],
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
        new Query,
        new QueryCollection(
            provider: ProductBagistoApiProvider::class,
            args: [
                'sortKey' => [
                    'type'        => 'String',
                    'description' => 'Sort products by field (TITLE, CREATED_AT, UPDATED_AT, PRICE, etc.)',
                ],
                'reverse' => [
                    'type'        => 'Boolean',
                    'description' => 'Reverse the sort order (true = descending, false = ascending)',
                ],
                'query' => [
                    'type'        => 'String',
                    'description' => 'Search query to filter products by SKU or name',
                ],
                'filter' => [
                    'type'        => 'String',
                    'description' => 'JSON filter object containing attribute filters (type, sku, category_id, price, color, name, etc.). Example: {"type":"configurable","sku":"ABC123"}',
                ],
                'first' => [
                    'type'        => 'Int',
                    'description' => 'Limit the number of products returned',
                ],
                'after' => [
                    'type'        => 'String',
                    'description' => 'Relay cursor for forward pagination',
                ],
                'before' => [
                    'type'        => 'String',
                    'description' => 'Relay cursor for backward pagination',
                ],
                'last' => [
                    'type'        => 'Int',
                    'description' => 'Return the last N items (used with before cursor)',
                ],
                'locale' => [
                    'type'        => 'String',
                    'description' => 'Fetch data products by locale',
                ],
                'channel' => [
                    'type'        => 'String',
                    'description' => 'Fetch data products by channel',
                ],
            ]
        ),
    ]
)]
class Product extends BaseProduct
{
    public $locale;

    public $channel;

    protected $with = [
        'attribute_family',
        'images',
        'attribute_values',
        'super_attributes',
        'variants',
        'price_indices',
    ];

    protected $appends = [
        'name', 'description', 'short_description', 'price', 'special_price',
        'weight', 'product_number', 'status', 'new', 'featured',
        'visible_individually', 'guest_checkout', 'manage_stock',
        'url_key', 'tax_category_id', 'special_price_from', 'special_price_to',
        'meta_title', 'meta_keywords',
        'cost', 'meta_description', 'length', 'width', 'height',
        'color', 'size', 'brand', 'locale', 'channel', 'description_html',
        'minimum_price', 'maximum_price', 'regular_minimum_price', 'regular_maximum_price', 'index',
    ];

    protected static array $systemAttributes = [
        'sku'                  => ['id' => 1],
        'name'                 => ['id' => 2],
        'url_key'              => ['id' => 3],
        'tax_category_id'      => ['id' => 4],
        'new'                  => ['id' => 5],
        'featured'             => ['id' => 6],
        'visible_individually' => ['id' => 7],
        'status'               => ['id' => 8],
        'short_description'    => ['id' => 9],
        'description'          => ['id' => 10],
        'price'                => ['id' => 11],
        'special_price'        => ['id' => 13],
        'special_price_from'   => ['id' => 14],
        'special_price_to'     => ['id' => 15],
        'meta_title'           => ['id' => 16],
        'meta_keywords'        => ['id' => 17],
        'weight'               => ['id' => 22],
        'guest_checkout'       => ['id' => 26],
        'product_number'       => ['id' => 27],
        'manage_stock'         => ['id' => 28],
        'cost'                 => ['id' => 12],
        'meta_description'     => ['id' => 18],
        'length'               => ['id' => 19],
        'width'                => ['id' => 20],
        'height'               => ['id' => 21],
        'color'                => ['id' => 23],
        'size'                 => ['id' => 24],
        'brand'                => ['id' => 25],
    ];

    #[ApiProperty(identifier: true, writable: false)]
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

    public function attribute_values(): HasMany
    {
        return $this->hasMany(AttributeValue::class, 'product_id');
    }

    /**
     * Get locale context.
     */
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

    /**
     * Get super attributes relationship (many-to-many).
     */
    public function super_attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_super_attributes');
    }

    /**
     * Get super attributes.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getSuper_attributes()
    {
        return $this->super_attributes;
    }

    /**
     * Set super attributes.
     */
    public function setSuper_attributes($value): void
    {
        $this->super_attributes = $value;
    }

    /**
     * Get product categories relationship.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Get product categories.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set product categories.
     */
    public function setCategories($value): void
    {
        $this->categories = $value;
    }

    /**
     * The images that belong to the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id')
            ->orderBy('position');
    }

    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getImages()
    {
        return $this->images;
    }

    public function setImages($value): void
    {
        $this->images = $value;
    }

    /**
     * The videos that belong to the product.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class, 'product_id')
            ->orderBy('position');
    }

    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getVideos()
    {
        return $this->videos;
    }

    public function setVideos($value): void
    {
        $this->videos = $value;
    }

    /**
     * The images that belong to the product.
     */
    public function base_image_url(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id')
            ->orderBy('position');
    }

    #[ApiProperty(
        writable: false,
        readable: true,
        required: true,
        readableLink: true
    )]
    public function getBase_image_url(): ProductImage
    {
        return $this->base_image_url;
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'product_channels', 'product_id', 'channel_id');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getChannels()
    {
        return $this->channels;
    }

    public function setChannels($value): void
    {
        $this->channels = $value;
    }

    /**
     * Get configurable product option index attribute.
     *
     * For configurable products, returns an index mapping variant IDs to their option values by attribute code.
     * Format: JSON string like { "588": { "color": 1, "size": 6 }, "589": { "color": 2, "size": 6 }, ... }
     *
     * This allows headless developers to identify which variant matches selected options.
     * Similar to Shop package's ConfigurableOption helper.
     */
    public function getIndexAttribute(): string
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
            if (! isset($index[$variant->id])) {
                $index[$variant->id] = [];
            }

            // Load variant's attribute values if needed
            if (! $variant->relationLoaded('attribute_values')) {
                $variant->load('attribute_values.attribute');
            }

            // Get the attribute value for each super attribute
            foreach ($variant->attribute_values as $attrValue) {
                // Only include super attributes (configurable attributes)
                if (in_array($attrValue->attribute_id, $superAttributeIds)) {
                    $attributeCode = $attributeCodeMap[$attrValue->attribute_id] ?? null;
                    if ($attributeCode) {
                        $index[$variant->id][$attributeCode] = $attrValue->value;
                    }
                }
            }
        }

        return json_encode($index);
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getIndex(): ?string
    {
        $indexJson = $this->getIndexAttribute();

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
    #[Groups(['mutation'])]
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
    #[Groups(['mutation'])]
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $value): void
    {
        $this->type = $value;
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: true
    )]
    #[Groups(['mutation'])]
    public function getAttribute_family(): ?AttributeFamily
    {
        return $this->attribute_family;
    }

    public function setAttribute_family(?AttributeFamily $value): void
    {
        $this->attribute_family = $value;
    }

    /**
     * Get attribute family relationship
     * Override to return BagistoApi AttributeFamily model
     */
    public function attribute_family(): BelongsTo
    {
        return $this->belongsTo(AttributeFamily::class, 'attribute_family_id');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: true
    )]
    public function getBookingProductsAttributes()
    {
        return $this->getBookingProducts();
    }

    /**
     * Get booking products.
     */
    public function getBookingProducts()
    {
        return $this->booking_products;
    }

    /**
     * Set booking products.
     */
    public function setBookingProducts($value): void
    {
        $this->booking_products = $value;
    }

    /**
     * Get the booking products relationship.
     */
    public function booking_products(): HasMany
    {
        return $this->hasMany(BookingProduct::class);
    }

    /**
     * Get bundle options.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getBundleOptions()
    {
        return $this->bundle_options;
    }

    public function setBundleOptions($value): void
    {
        $this->bundle_options = $value;
    }

    /**
     * Get the bundle options relationship.
     */
    public function bundle_options(): HasMany
    {
        return $this->hasMany(ProductBundleOption::class);
    }

    public function grouped_products(): HasMany
    {
        return $this->hasMany(ProductGroupedProduct::class, 'product_id');
    }

    public function downloadable_links(): HasMany
    {
        return $this->hasMany(ProductDownloadableLink::class, 'product_id');
    }

    public function downloadable_samples(): HasMany
    {
        return $this->hasMany(ProductDownloadableSample::class, 'product_id');
    }

    /**
     * Get downloadable samples.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getDownloadableSamples()
    {
        return $this->downloadable_samples;
    }

    public function setDownloadableSamples($value): void
    {
        $this->downloadable_samples = $value;
    }

    /**
     * The customizable options that belong to the product.
     */
    public function customizable_options(): HasMany
    {
        return $this->hasMany(ProductCustomizableOption::class, 'product_id')
            ->orderBy('sort_order');
    }

    /**
     * Get customizable options.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getCustomizable_options()
    {
        // Eager load prices to ensure they're properly constrained
        return $this->customizable_options()
            ->with('customizable_option_prices')
            ->get();
    }

    public function setCustomizable_options($value): void
    {
        $this->customizable_options = $value;
    }

    /**
     * Get name attribute.
     */
    public function getNameAttribute(): ?string
    {
        return $this->getSystemAttributeValue('name');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    public function getName(): ?string
    {
        return $this->getNameAttribute();
    }

    public function setName(?string $value): void
    {
        $this->setSystemAttributeValue('name', $value);
    }

    // ========================================
    // URL Key (text, per locale) - Only for update
    // ========================================

    public function getUrlKeyAttribute(): ?string
    {
        return $this->getSystemAttributeValue('url_key');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    public function getUrl_key(): ?string
    {
        return $this->getUrlKeyAttribute();
    }

    public function setUrl_key(?string $value): void
    {
        $this->setSystemAttributeValue('url_key', $value);
    }

    // ========================================
    // Status (boolean, per channel)
    // ========================================

    public function getStatusAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('status');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getStatus(): ?bool
    {
        return $this->getStatusAttribute();
    }

    public function setStatus(?bool $value): void
    {
        $this->setSystemAttributeValue('status', $value);
    }

    // ========================================
    // Description (textarea, per locale)
    // ========================================

    public function getDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('description');
    }

    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getDescription(): ?string
    {
        return $this->getDescriptionAttribute();
    }

    public function setDescription(?string $value): void
    {
        $this->setSystemAttributeValue('description', $value);
    }

    /**
     * Laravel accessor for description_html appended attribute
     */
    public function getDescriptionHtmlAttribute(): ?string
    {
        return $this->getDescriptionAttribute();
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getDescription_html(): ?string
    {
        return $this->getDescription_htmlAttribute();
    }

    public function getShortDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('short_description');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false,
        schema: ['type' => 'string', 'nullable' => true],
        openapiContext: ['nullable' => true],
        jsonSchemaContext: ['type' => 'string', 'nullable' => true]
    )]
    public function getShort_description(): ?string
    {
        return $this->getShort_descriptionAttribute();
    }

    public function setShort_description(?string $value): void
    {
        $this->setSystemAttributeValue('short_description', $value);
    }

    public function getPriceAttribute(): ?float
    {
        return floatval($this->getSystemAttributeValue('price'));
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getPrice(): ?float
    {
        return $this->getPriceAttribute();
    }

    public function setPrice(?float $value): void
    {
        $this->setSystemAttributeValue('price', $value);
    }

    public function getSpecialPriceAttribute(): ?float
    {
        return floatval($this->getSystemAttributeValue('special_price'));
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getSpecial_price(): ?float
    {
        return $this->getSpecialPriceAttribute();
    }

    public function setSpecial_price(?float $value): void
    {
        $this->setSystemAttributeValue('special_price', $value);
    }

    public function getWeightAttribute(): ?string
    {
        return $this->getSystemAttributeValue('weight');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getWeight(): ?string
    {
        return $this->getWeightAttribute();
    }

    public function setWeight(?string $value): void
    {
        $this->setSystemAttributeValue('weight', $value);
    }

    public function getProductNumberAttribute(): ?string
    {
        return $this->getSystemAttributeValue('product_number');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getProduct_number(): ?string
    {
        return $this->getProductNumberAttribute();
    }

    public function setProduct_number(?string $value): void
    {
        $this->setSystemAttributeValue('product_number', $value);
    }

    public function getNewAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('new');
    }

    #[ApiProperty(
        writable: true,
        readable: true,
        required: false,
        schema: ['type' => 'boolean', 'nullable' => true],
    )]
    public function getNew(): bool
    {
        return $this->getNewAttribute();
    }

    public function setNew(bool $value): void
    {
        $this->setSystemAttributeValue('new', $value);
    }

    public function getFeaturedAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('featured');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getFeatured(): ?bool
    {
        return $this->getFeaturedAttribute();
    }

    public function setFeatured(?bool $value): void
    {
        $this->setSystemAttributeValue('featured', $value);
    }

    // ========================================
    // Visible Individually (boolean)
    // ========================================

    public function getVisibleIndividuallyAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('visible_individually');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getVisible_individually(): ?bool
    {
        return $this->getVisibleIndividuallyAttribute();
    }

    public function setVisible_individually(?bool $value): void
    {
        $this->setSystemAttributeValue('visible_individually', $value);
    }

    // ========================================
    // Guest Checkout (boolean)
    // ========================================

    public function getGuestCheckoutAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('guest_checkout');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getGuest_checkout(): ?bool
    {
        return $this->getGuestCheckoutAttribute();
    }

    public function setGuest_checkout(?bool $value): void
    {
        $this->setSystemAttributeValue('guest_checkout', $value);
    }

    // ========================================
    // Manage Stock (boolean, per channel)
    // ========================================

    public function getManageStockAttribute(): ?bool
    {
        return $this->getSystemAttributeValue('manage_stock');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getManage_stock(): ?bool
    {
        return $this->getManageStockAttribute();
    }

    public function setManage_stock(?bool $value): void
    {
        $this->setSystemAttributeValue('manage_stock', $value);
    }

    // ========================================
    // Meta Title (textarea, per locale)
    // ========================================

    public function getMetaTitleAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_title');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_title(): ?string
    {
        return $this->getMetaTitleAttribute();
    }

    public function setMeta_title(?string $value): void
    {
        $this->setSystemAttributeValue('meta_title', $value);
    }

    // ========================================
    // Meta Keywords (textarea, per locale)
    // ========================================

    public function getMetaKeywordsAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_keywords');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_keywords(): ?string
    {
        return $this->getMetaKeywordsAttribute();
    }

    public function setMeta_keywords(?string $value): void
    {
        $this->setSystemAttributeValue('meta_keywords', $value);
    }

    // ========================================
    // Tax Category ID (select, per channel)
    // ========================================

    public function getTaxCategoryIdAttribute(): ?int
    {
        return (int) $this->getSystemAttributeValue('tax_category_id');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getTax_category_id(): ?int
    {
        return $this->getTaxCategoryIdAttribute();
    }

    public function setTax_category_id(?int $value): void
    {
        $this->setSystemAttributeValue('tax_category_id', $value);
    }

    // ========================================
    // Special Price From (date, per channel)
    // ========================================

    public function getSpecialPriceFromAttribute(): ?string
    {
        return $this->getSystemAttributeValue('special_price_from');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getSpecial_price_from(): ?string
    {
        return $this->getSpecialPriceFromAttribute();
    }

    public function setSpecial_price_from(?string $value): void
    {
        $this->setSystemAttributeValue('special_price_from', $value);
    }

    // ========================================
    // Special Price To (date, per channel)
    // ========================================

    public function getSpecialPriceToAttribute(): ?string
    {
        return $this->getSystemAttributeValue('special_price_to');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getSpecial_price_to(): ?string
    {
        return $this->getSpecialPriceToAttribute();
    }

    public function setSpecial_price_to(?string $value): void
    {
        $this->setSystemAttributeValue('special_price_to', $value);
    }

    // ========================================
    // Cost (price) - User-defined
    // ========================================

    public function getCostAttribute()
    {
        return floatval($this->getSystemAttributeValue('cost'));
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getCost(): ?float
    {
        return $this->getCostAttribute();
    }

    public function setCost(?float $value): void
    {
        $this->setSystemAttributeValue('cost', $value);
    }

    // ========================================
    // Meta Description (textarea, per locale) - User-defined
    // ========================================

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->getSystemAttributeValue('meta_description');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getMeta_description(): ?string
    {
        return $this->getMetaDescriptionAttribute();
    }

    public function setMeta_description(?string $value): void
    {
        $this->setSystemAttributeValue('meta_description', $value);
    }

    // ========================================
    // Length (text) - User-defined
    // ========================================

    public function getLengthAttribute(): ?string
    {
        return $this->getSystemAttributeValue('length');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getLength(): ?string
    {
        return $this->getLengthAttribute();
    }

    public function setLength(?string $value): void
    {
        $this->setSystemAttributeValue('length', $value);
    }

    // ========================================
    // Width (text) - User-defined
    // ========================================

    public function getWidthAttribute(): ?string
    {
        return $this->getSystemAttributeValue('width');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getWidth(): ?string
    {
        return $this->getWidthAttribute();
    }

    public function setWidth(?string $value): void
    {
        $this->setSystemAttributeValue('width', $value);
    }

    // ========================================
    // Height (text) - User-defined
    // ========================================

    public function getHeightAttribute(): ?string
    {
        return $this->getSystemAttributeValue('height');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getHeight(): ?string
    {
        return $this->getHeightAttribute();
    }

    public function setHeight(?string $value): void
    {
        $this->setSystemAttributeValue('height', $value);
    }

    // ========================================
    // Color (select) - User-defined
    // ========================================

    public function getColorAttribute()
    {
        return $this->getSystemAttributeValue('color');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getColor(): ?int
    {
        return $this->getColorAttribute();
    }

    public function setColor(?int $value): void
    {
        $this->setSystemAttributeValue('color', $value);
    }

    // ========================================
    // Size (select) - User-defined
    // ========================================

    public function getSizeAttribute()
    {
        $sizeValue = $this->getSystemAttributeValue('size');

        return is_array($sizeValue) ? implode(',', $sizeValue) : $sizeValue;
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getSize(): ?int
    {
        return $this->getSizeAttribute();
    }

    public function setSize(?int $value): void
    {
        $this->setSystemAttributeValue('size', $value);
    }

    // ========================================
    // Brand (select) - User-defined
    // ========================================

    public function getBrandAttribute()
    {
        return $this->getSystemAttributeValue('brand');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getBrand(): ?int
    {
        return $this->getBrandAttribute();
    }

    public function setBrand(?int $value): void
    {
        $this->setSystemAttributeValue('brand', $value);
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getReviews()
    {
        return function ($source, array $args = [], $context = null) {
            $relation = $this->reviews();

            if (isset($args['status'])) {
                $relation = $relation->where('status', $args['status']);
            }

            if (isset($args['first']) && is_numeric($args['first'])) {
                $relation = $relation->limit((int) $args['first']);
            }

            if (empty($args) && $this->relationLoaded('reviews')) {
                return $this->reviews;
            }

            return $relation->get();
        };
    }

    /**
     * Get the product reviews that owns the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Cache for attribute values to avoid repeated lookups
     */
    protected array $attributeValueCache = [];

    /**
     * Get a system attribute value from product_attribute_values
     * This reads from the database when querying
     * OPTIMIZED: Uses memoization to cache attribute values within the same request
     */
    protected function getSystemAttributeValue(string $attributeCode): mixed
    {
        // Check cache first
        if (array_key_exists($attributeCode, $this->attributeValueCache)) {
            return $this->attributeValueCache[$attributeCode];
        }

        // If value was set via setter (during input), return it from temporary storage
        $tempKey = "_temp_{$attributeCode}";

        if (isset($this->attributes[$tempKey])) {
            return $this->attributeValueCache[$attributeCode] = $this->attributes[$tempKey];
        }

        // Otherwise, read from database via relationship
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

        $localeVariants[] = null;

        $channelVariants = [$currentChannel, null];

        foreach ($localeVariants as $localeVariant) {
            foreach ($channelVariants as $channelVariant) {
                $query = $this->attribute_values->where('attribute_id', $attrConfig['id']);

                if ($localeVariant === null) {
                    $query = $query->whereNull('locale');
                } else {
                    $query = $query->where('locale', $localeVariant);
                }

                if ($channelVariant === null) {
                    $query = $query->whereNull('channel');
                } else {
                    $query = $query->where('channel', $channelVariant);
                }

                $attributeValue = $query->first();

                if ($attributeValue) {
                    break 2;
                }
            }
        }

        if ($attributeValue && $attributeValue?->integer_value && in_array($attributeValue?->attribute?->type, ['select', 'multiselect', 'checkbox'])) {
            $attributeValue->setValue($attributeValue->attribute->options()->where('id', $attributeValue->value)->first()?->label);
        }

        return $this->attributeValueCache[$attributeCode] = ($attributeValue ? $attributeValue->value : '');
    }

    /**
     * Set a system attribute value (will be processed by ProductProcessor)
     * This stores in temporary attributes array for processing later
     */
    protected function setSystemAttributeValue(string $attributeCode, mixed $value): void
    {
        $tempKey = "_temp_{$attributeCode}";
        $this->attributes[$tempKey] = $value;
    }

    public function related_products(): BelongsToMany
    {
        return $this->belongsToMany(static::class, 'product_relations', 'parent_id', 'child_id');
    }

    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getRelatedProducts()
    {
        return function ($source, array $args = [], $context = null) {

            $relation = $source->related_products();

            $total = $relation->count();

            $limit = $args['first'] ?? $args['last'] ?? 30;

            $items = $relation->limit($limit)->get();

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $limit,
                1,
                ['path' => '/']
            );
        };
    }

    public function up_sells(): BelongsToMany
    {
        return $this->belongsToMany(static::class, 'product_up_sells', 'parent_id', 'child_id');
    }

    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getUpSells()
    {
        // Return a Closure so ResourceFieldResolver invokes it with ($source, $args, $context)
        return function ($source, array $args = [], $context = null) {
            $relation = $source->up_sells();

            // Get total count before applying limit
            $total = $relation->count();

            // Apply first/last pagination if provided
            $limit = $args['first'] ?? $args['last'] ?? 30;
            $items = $relation->limit($limit)->get();

            // Return a LengthAwarePaginator so ApiPlatform can compute totalCount
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $limit,
                1,
                ['path' => '/']
            );
        };
    }

    public function cross_sells(): BelongsToMany
    {
        return $this->belongsToMany(static::class, 'product_cross_sells', 'parent_id', 'child_id');
    }

    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getCrossSells()
    {
        // Return a Closure so ResourceFieldResolver invokes it with ($source, $args, $context)
        return function ($source, array $args = [], $context = null) {
            $relation = $source->cross_sells();

            // Get total count before applying limit
            $total = $relation->count();

            // Apply first/last pagination if provided
            $limit = $args['first'] ?? $args['last'] ?? 30;
            $items = $relation->limit($limit)->get();

            // Return a LengthAwarePaginator so ApiPlatform can compute totalCount
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $limit,
                1,
                ['path' => '/']
            );
        };
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getProductPrices()
    {
        return $this->product_prices;
    }

    // ========================================
    // Minimum and Maximum Price (computed)
    // ========================================

    /**
     * Laravel accessor for minimum_price attribute.
     * Get product minimum price based on price index.
     * Falls back to base price if no price index is available.
     */
    public function getMinimumPriceAttribute(): float
    {
        try {
            // Load price indices if not already loaded
            if (! $this->relationLoaded('price_indices')) {
                $this->load('price_indices');
            }

            // Get current channel and customer group
            $currentChannel = core()->getCurrentChannel();
            $customerGroup = resolve('Webkul\Customer\Repositories\CustomerRepository')->getCurrentGroup();

            if (! $currentChannel || ! $customerGroup) {
                return floatval($this->price ?? 0);
            }

            // Get price index for current channel and customer group
            $priceIndex = $this->price_indices
                ->where('channel_id', $currentChannel->id)
                ->where('customer_group_id', $customerGroup->id)
                ->first();

            if ($priceIndex) {
                return floatval($priceIndex->min_price);
            }

            // Fallback to base price
            return floatval($this->price ?? 0);
        } catch (\Exception $e) {
            // If any error occurs, return base price
            return floatval($this->price ?? 0);
        }
    }

    /**
     * Get product minimum price for BagistoApi API.
     * Exposed to BagistoApi schema via ApiProperty attribute.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getMinimum_price(): float
    {
        return $this->getMinimumPriceAttribute();
    }

    /**
     * Laravel accessor for maximum_price attribute.
     * Get product maximum price based on price index.
     * Falls back to base price if no price index is available.
     */
    public function getMaximumPriceAttribute(): float
    {
        try {
            // Load price indices if not already loaded
            if (! $this->relationLoaded('price_indices')) {
                $this->load('price_indices');
            }

            // Get current channel and customer group
            $currentChannel = core()->getCurrentChannel();
            $customerGroup = resolve('Webkul\Customer\Repositories\CustomerRepository')->getCurrentGroup();

            if (! $currentChannel || ! $customerGroup) {
                return floatval($this->price ?? 0);
            }

            // Get price index for current channel and customer group
            $priceIndex = $this->price_indices
                ->where('channel_id', $currentChannel->id)
                ->where('customer_group_id', $customerGroup->id)
                ->first();

            if ($priceIndex) {
                return floatval($priceIndex->max_price);
            }

            // Fallback to base price
            return floatval($this->price ?? 0);
        } catch (\Exception $e) {
            // If any error occurs, return base price
            return floatval($this->price ?? 0);
        }
    }

    /**
     * Get product maximum price for BagistoApi API.
     * Exposed to BagistoApi schema via ApiProperty attribute.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getMaximum_price(): float
    {
        return $this->getMaximumPriceAttribute();
    }

    /**
     * Laravel accessor for regular_minimum_price attribute.
     * Get product regular minimum price based on price index.
     * Falls back to base price if no price index is available.
     */
    public function getRegularMinimumPriceAttribute(): float
    {
        try {
            // Load price indices if not already loaded
            if (! $this->relationLoaded('price_indices')) {
                $this->load('price_indices');
            }

            // Get current channel and customer group
            $currentChannel = core()->getCurrentChannel();
            $customerGroup = resolve('Webkul\Customer\Repositories\CustomerRepository')->getCurrentGroup();

            if (! $currentChannel || ! $customerGroup) {
                return floatval($this->price ?? 0);
            }

            // Get price index for current channel and customer group
            $priceIndex = $this->price_indices
                ->where('channel_id', $currentChannel->id)
                ->where('customer_group_id', $customerGroup->id)
                ->first();

            if ($priceIndex) {
                return floatval($priceIndex->regular_min_price);
            }

            // Fallback to base price
            return floatval($this->price ?? 0);
        } catch (\Exception $e) {
            // If any error occurs, return base price
            return floatval($this->price ?? 0);
        }
    }

    /**
     * Get product regular minimum price for BagistoApi API.
     * Exposed to BagistoApi schema via ApiProperty attribute.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getRegular_minimum_price(): float
    {
        return $this->getRegularMinimumPriceAttribute();
    }

    /**
     * Laravel accessor for regular_maximum_price attribute.
     * Get product regular maximum price based on price index.
     * Falls back to base price if no price index is available.
     */
    public function getRegularMaximumPriceAttribute(): float
    {
        try {
            // Load price indices if not already loaded
            if (! $this->relationLoaded('price_indices')) {
                $this->load('price_indices');
            }

            // Get current channel and customer group
            $currentChannel = core()->getCurrentChannel();
            $customerGroup = resolve('Webkul\Customer\Repositories\CustomerRepository')->getCurrentGroup();

            if (! $currentChannel || ! $customerGroup) {
                return floatval($this->price ?? 0);
            }

            // Get price index for current channel and customer group
            $priceIndex = $this->price_indices
                ->where('channel_id', $currentChannel->id)
                ->where('customer_group_id', $customerGroup->id)
                ->first();

            if ($priceIndex) {
                return floatval($priceIndex->regular_max_price);
            }

            // Fallback to base price
            return floatval($this->price ?? 0);
        } catch (\Exception $e) {
            // If any error occurs, return base price
            return floatval($this->price ?? 0);
        }
    }

    /**
     * Get product regular maximum price for BagistoApi API.
     * Exposed to BagistoApi schema via ApiProperty attribute.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getRegular_maximum_price(): float
    {
        return $this->getRegularMaximumPriceAttribute();
    }
}
