<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\Product\Models\ProductBundleOption as BaseProductBundleOption;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
    ],
    graphQlOperations: [
        new QueryCollection,
        new Query,
    ]
)]
class ProductBundleOption extends BaseProductBundleOption
{
    /**
     * Translation model class.
     */
    protected $translationModel = ProductBundleOptionTranslation::class;

    /**
     * Get the bundle option identifier.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the parent product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the label.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getLabel(): ?string
    {
        return $this->label ?? null;
    }

    /**
     * Set the label.
     */
    public function setLabel(?string $value): void
    {
        if ($value) {
            $translation = $this->translate();
            if ($translation) {
                $translation->label = $value;
            }
        }
    }

    /**
     * Get the option type.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set the option type.
     */
    public function setType(?string $value): void
    {
        $this->type = $value;
    }

    /**
     * Check if option is required.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getIsRequired(): ?bool
    {
        return (bool) $this->is_required;
    }

    /**
     * Set if option is required.
     */
    public function setIsRequired(?bool $value): void
    {
        $this->is_required = $value;
    }

    /**
     * Get the sort order.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    /**
     * Get the bundle option products.
     */
    public function bundle_option_products(): HasMany
    {
        return $this->hasMany(ProductBundleOptionProduct::class, 'product_bundle_option_id');
    }
}
