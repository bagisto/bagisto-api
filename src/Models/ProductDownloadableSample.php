<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\State\DownloadableSamplesProvider;
use Webkul\Product\Models\ProductDownloadableSample as BaseProductDownloadableSample;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/product-downloadable-samples/{id}',
    operations: [
        new GetCollection(uriTemplate: '/product-downloadable-samples'),
        new Get(uriTemplate: '/product-downloadable-samples/{id}'),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: DownloadableSamplesProvider::class,
            links: [
                new Link(
                    fromProperty: 'downloadableSamples',
                    fromClass: Product::class,
                    toClass: self::class,
                    identifiers: ['product_id'],
                ),
            ],
        ),
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
class ProductDownloadableSample extends BaseProductDownloadableSample
{
    public function toArray(): array
    {
        $array = parent::toArray();

        // Ensure title is included in serialization from translations
        if (! isset($array['title']) && $this->title) {
            $array['title'] = $this->title;
        }

        // Override file_url to return null instead of empty string when no file
        if (! $this->file || empty($this->file)) {
            $array['file_url'] = null;
        }

        return $array;
    }

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function translation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductDownloadableSampleTranslation::class, 'product_downloadable_sample_id');
    }

    /**
     * Get the file URL with full domain.
     */
    public function file_url(): ?string
    {
        if (! $this->file || empty($this->file)) {
            return null;
        }

        $url = Storage::url($this->file);

        // Ensure full URL with domain if not already absolute
        if (! $this->isAbsoluteUrl($url)) {
            $url = config('app.url').$url;
        }

        return $url;
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $value): void
    {
        $this->title = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $value): void
    {
        $this->url = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $value): void
    {
        $this->file = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(?string $value): void
    {
        $this->file_name = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $value): void
    {
        $this->type = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    public function setSortOrder(?int $value): void
    {
        $this->sort_order = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(?int $value): void
    {
        $this->product_id = $value;
    }

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
