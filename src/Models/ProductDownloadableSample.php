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
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\DownloadableSamplesProvider;
use Webkul\Product\Models\ProductDownloadableSample as BaseProductDownloadableSample;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/product-downloadable-samples/{id}',
    operations: [
        new GetCollection(
            uriTemplate: '/product-downloadable-samples',
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'List downloadable samples',
                description: 'Returns sample/preview files for downloadable-type products. Visible to all visitors before purchase.',
                responses: [
                    '200' => new Response(
                        description: 'List of downloadable samples',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'file' => 'product_downloadable_links/2506/1apTXUkt2ugCISKHadT5Fmp4EwU7YeWYY2wb4mNs.pdf',
                                        'fileName' => 'document.pdf',
                                        'type' => 'file',
                                        'sortOrder' => 0,
                                        'createdAt' => '2026-04-03T00:14:55+05:30',
                                        'updatedAt' => '2026-04-03T00:14:55+05:30',
                                        'fileUrl' => 'http://localhost:8000/api/downloadable/download-sample/sample/1',
                                        'product' => '/api/shop/products/2506',
                                        'translation' => '/api/shop/product_downloadable_sample_translations/1',
                                        'translations' => ['/api/shop/product_downloadable_sample_translations/1'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/product-downloadable-samples/{id}',
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'Get a single downloadable sample',
                description: 'Returns one sample/preview file for a downloadable-type product. Visible to all visitors before purchase.',
                responses: [
                    '200' => new Response(
                        description: 'The downloadable sample',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'file' => 'product_downloadable_links/2506/1apTXUkt2ugCISKHadT5Fmp4EwU7YeWYY2wb4mNs.pdf',
                                    'fileName' => 'document.pdf',
                                    'type' => 'file',
                                    'sortOrder' => 0,
                                    'createdAt' => '2026-04-03T00:14:55+05:30',
                                    'updatedAt' => '2026-04-03T00:14:55+05:30',
                                    'fileUrl' => 'http://localhost:8000/api/downloadable/download-sample/sample/1',
                                    'product' => '/api/shop/products/2506',
                                    'translation' => '/api/shop/product_downloadable_sample_translations/1',
                                    'translations' => ['/api/shop/product_downloadable_sample_translations/1'],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Downloadable sample not found.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: DownloadableSamplesProvider::class,
            description: 'Every downloadable sample of every downloadable product. Read one product\'s samples through its `downloadableSamples` field instead, which returns the first page of that product. Standard cursor pagination with `first`/`after` or `last`/`before`, 10 rows by default and at most 50. Needs the X-STOREFRONT-KEY header.',
            links: [
                new Link(
                    fromProperty: 'downloadableSamples',
                    fromClass: Product::class,
                    toClass: self::class,
                    identifiers: ['product_id'],
                ),
            ],
        ),
        new Query(
            resolver: BaseQueryItemResolver::class,
            description: 'One downloadable sample. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header.',
        ),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductDownloadableSample',
    uriTemplate: '/products/{productId}/downloadable-samples',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'downloadable_samples',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'List downloadable samples for a product',
                description: 'Downloadable-type only. Returns sample/preview files visible to all visitors before purchase.',
                responses: [
                    '200' => new Response(
                        description: 'List of downloadable samples for the product',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'file' => 'product_downloadable_links/2506/1apTXUkt2ugCISKHadT5Fmp4EwU7YeWYY2wb4mNs.pdf',
                                        'fileName' => 'document.pdf',
                                        'type' => 'file',
                                        'sortOrder' => 0,
                                        'createdAt' => '2026-04-03T00:14:55+05:30',
                                        'updatedAt' => '2026-04-03T00:14:55+05:30',
                                        'fileUrl' => 'http://localhost:8000/api/downloadable/download-sample/sample/1',
                                        'product' => '/api/shop/products/2506',
                                        'translation' => '/api/shop/product_downloadable_sample_translations/1',
                                        'translations' => ['/api/shop/product_downloadable_sample_translations/1'],
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
class ProductDownloadableSample extends BaseProductDownloadableSample
{
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
            ! $this->file
            || empty($this->file)
        ) {
            $array['file_url'] = null;
        }

        return $array;
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
        return $this->hasOne(ProductDownloadableSampleTranslation::class, 'product_downloadable_sample_id');
    }

    /**
     * Get the file URL via the REST download endpoint.
     */
    public function file_url(): ?string
    {
        if (
            ! $this->file
            || empty($this->file)
        ) {
            return null;
        }

        return url('/api/downloadable/download-sample/sample/'.$this->id);
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
     * Get url.
     */
    #[ApiProperty(writable: true, readable: true)]
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
    #[ApiProperty(writable: true, readable: true)]
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
    #[ApiProperty(writable: true, readable: true)]
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
    #[ApiProperty(writable: false, readable: true)]
    public function getFileUrl(): ?string
    {
        return $this->file_url();
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
