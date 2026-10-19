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
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\DownloadableLinksProvider;
use Webkul\Product\Models\ProductDownloadableLink as BaseProductDownloadableLink;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/product-downloadable-links/{id}',
    operations: [
        new GetCollection(
            uriTemplate: '/product-downloadable-links',
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'List downloadable links',
                description: 'Returns the downloadable file links for downloadable-type products. Customers receive access to these after purchase.',
                responses: [
                    '200' => new Response(
                        description: 'Downloadable links list',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 2,
                                        'url' => 'https://resources.saylor.org/wwwresources/archived/site/textbooks/Personal%20Finance.pdf',
                                        'type' => 'url',
                                        'price' => 69,
                                        'sampleFile' => 'product_downloadable_links/2506/4aUxeYumTemSR3QwHHHGmdiHBG2qWek3KDR8fhYK.pdf',
                                        'sampleFileName' => 'Personal Finance.pdf',
                                        'sampleType' => 'file',
                                        'downloads' => 10,
                                        'sortOrder' => 0,
                                        'createdAt' => '2026-04-03T00:14:55+05:30',
                                        'updatedAt' => '2026-04-03T00:14:55+05:30',
                                        'formattedPrice' => '$69.00',
                                        'fileUrl' => 'http://localhost:8000/storage/',
                                        'sampleFileUrl' => 'http://localhost:8000/api/downloadable/download-sample/link/2',
                                        'product' => '/api/shop/products/2506',
                                        'translation' => '/api/shop/product_downloadable_link_translations/2',

                                        'translations' => [
                                            '/api/shop/product_downloadable_link_translations/2',
                                            '/api/shop/product_downloadable_link_translations/3',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/product-downloadable-links/{id}',
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'Get a single downloadable link',
                responses: [
                    '200' => new Response(
                        description: 'Downloadable link',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 2,
                                    'url' => 'https://resources.saylor.org/wwwresources/archived/site/textbooks/Personal%20Finance.pdf',
                                    'type' => 'url',
                                    'price' => 69,
                                    'sampleFile' => 'product_downloadable_links/2506/4aUxeYumTemSR3QwHHHGmdiHBG2qWek3KDR8fhYK.pdf',
                                    'sampleFileName' => 'Personal Finance.pdf',
                                    'sampleType' => 'file',
                                    'downloads' => 10,
                                    'sortOrder' => 0,
                                    'createdAt' => '2026-04-03T00:14:55+05:30',
                                    'updatedAt' => '2026-04-03T00:14:55+05:30',
                                    'formattedPrice' => '$69.00',
                                    'fileUrl' => 'http://localhost:8000/storage/',
                                    'sampleFileUrl' => 'http://localhost:8000/api/downloadable/download-sample/link/2',
                                    'product' => '/api/shop/products/2506',
                                    'translation' => '/api/shop/product_downloadable_link_translations/2',

                                    'translations' => [
                                        '/api/shop/product_downloadable_link_translations/2',
                                        '/api/shop/product_downloadable_link_translations/3',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Downloadable link not found',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Downloadable link not found.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: DownloadableLinksProvider::class,
            links: [
                new Link(
                    fromProperty: 'downloadableLinks',
                    fromClass: Product::class,
                    toClass: self::class,
                    identifiers: ['product_id'],
                ),
            ],
        ),
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductDownloadableLink',
    uriTemplate: '/products/{productId}/downloadable-links',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'downloadable_links',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'List downloadable links for a product',
                description: 'Downloadable-type only. Returns the downloadable file/URL links a customer receives after purchasing this downloadable product.',
                responses: [
                    '200' => new Response(
                        description: 'Downloadable links for the product',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 2,
                                        'url' => 'https://resources.saylor.org/wwwresources/archived/site/textbooks/Personal%20Finance.pdf',
                                        'type' => 'url',
                                        'price' => 69,
                                        'sampleFile' => 'product_downloadable_links/2506/4aUxeYumTemSR3QwHHHGmdiHBG2qWek3KDR8fhYK.pdf',
                                        'sampleFileName' => 'Personal Finance.pdf',
                                        'sampleType' => 'file',
                                        'downloads' => 10,
                                        'sortOrder' => 0,
                                        'createdAt' => '2026-04-03T00:14:55+05:30',
                                        'updatedAt' => '2026-04-03T00:14:55+05:30',
                                        'formattedPrice' => '$69.00',
                                        'fileUrl' => 'http://localhost:8000/storage/',
                                        'sampleFileUrl' => 'http://localhost:8000/api/downloadable/download-sample/link/2',
                                        'product' => '/api/shop/products/2506',
                                        'translation' => '/api/shop/product_downloadable_link_translations/2',

                                        'translations' => [
                                            '/api/shop/product_downloadable_link_translations/2',
                                            '/api/shop/product_downloadable_link_translations/3',
                                        ],
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
class ProductDownloadableLink extends BaseProductDownloadableLink
{
    /**
     * Paid link content is served to buyers only, never with the catalog.
     *
     * @var array<int, string>
     */
    protected $hidden = ['url', 'file', 'file_name'];

    /**
     * Get the array representation.
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        if (
            ! isset($array['title'])
            && $this->title
        ) {
            $array['title'] = $this->title;
        }

        if (
            ! $this->sample_file
            || empty($this->sample_file)
        ) {
            $array['sample_file_url'] = null;
        }

        return $array;
    }

    /**
     * Eloquent accessor to convert price to the selected currency.
     */
    public function getPriceAttribute($value): ?float
    {
        return $value !== null ? (float) core()->convertPrice((float) $value) : null;
    }

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Translation.
     */
    public function translation(): HasOne
    {
        return $this->hasOne(ProductDownloadableLinkTranslation::class, 'product_downloadable_link_id');
    }

    /**
     * Override parent's sample_file_url to return the REST download endpoint.
     * Sample files for links are stored on the private disk,.
     */
    public function sample_file_url(): string
    {
        if (
            ! $this->sample_file
            || empty($this->sample_file)
        ) {
            return '';
        }

        return url('/api/downloadable/download-sample/link/'.$this->id);
    }

    /**
     * Get title.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set title.
     */
    public function setTitle(?string $value): void
    {
        $this->title = $value;
    }

    /**
     * Get price.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getPrice(): ?float
    {
        return (float) $this->price;
    }

    /**
     * Set price.
     */
    public function setPrice(?float $value): void
    {
        $this->price = $value;
    }

    /**
     * Get formatted price attribute.
     */
    public function getFormattedPriceAttribute(): ?string
    {
        $price = $this->getPrice();

        return $price !== null ? core()->formatPrice($price) : null;
    }

    /**
     * Get formatted price.
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getFormatted_price(): ?string
    {
        return $this->getFormattedPriceAttribute();
    }

    /**
     * Get url.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set url.
     */
    public function setUrl(?string $value): void
    {
        $this->url = $value;
    }

    /**
     * Get file.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * Set file.
     */
    public function setFile(?string $value): void
    {
        $this->file = $value;
    }

    /**
     * Get file name.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    /**
     * Set file name.
     */
    public function setFileName(?string $value): void
    {
        $this->file_name = $value;
    }

    /**
     * Get type.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set type.
     */
    public function setType(?string $value): void
    {
        $this->type = $value;
    }

    /**
     * Get sample url.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSampleUrl(): ?string
    {
        return $this->sample_url;
    }

    /**
     * Set sample url.
     */
    public function setSampleUrl(?string $value): void
    {
        $this->sample_url = $value;
    }

    /**
     * Get sample file.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSampleFile(): ?string
    {
        return $this->sample_file;
    }

    /**
     * Set sample file.
     */
    public function setSampleFile(?string $value): void
    {
        $this->sample_file = $value;
    }

    /**
     * Get sample file name.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSampleFileName(): ?string
    {
        return $this->sample_file_name;
    }

    /**
     * Set sample file name.
     */
    public function setSampleFileName(?string $value): void
    {
        $this->sample_file_name = $value;
    }

    /**
     * Get sample type.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSampleType(): ?string
    {
        return $this->sample_type;
    }

    /**
     * Set sample type.
     */
    public function setSampleType(?string $value): void
    {
        $this->sample_type = $value;
    }

    /**
     * Get sort order.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    /**
     * Set sort order.
     */
    public function setSortOrder(?int $value): void
    {
        $this->sort_order = $value;
    }

    /**
     * Get downloads.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getDownloads(): ?int
    {
        return $this->downloads;
    }

    /**
     * Set downloads.
     */
    public function setDownloads(?int $value): void
    {
        $this->downloads = $value;
    }

    /**
     * Get product id.
     */
    #[ApiProperty(writable: true, readable: true)]
    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    /**
     * Set product id.
     */
    public function setProductId(?int $value): void
    {
        $this->product_id = $value;
    }

    /**
     * Get file url.
     */
    #[ApiProperty(writable: false, readable: false)]
    public function getFileUrl(): ?string
    {
        if (! $this->file) {
            return null;
        }

        $url = Storage::url($this->file);

        if (! $this->isAbsoluteUrl($url)) {
            $url = config('app.url').$url;
        }

        return $url;
    }

    /**
     * Get sample file url.
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getSampleFileUrl(): ?string
    {
        $url = $this->sample_file_url();

        if (
            ! $url
            || $url === ''
        ) {
            return null;
        }

        return $url;
    }

    /**
     * Check if the given URL is already absolute (has a protocol).
     */
    private function isAbsoluteUrl(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }
}
