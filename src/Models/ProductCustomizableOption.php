<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\Product\Models\ProductCustomizableOption as BaseProductCustomizableOption;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
)]
class ProductCustomizableOption extends BaseProductCustomizableOption
{
    /**
     * Get the product that owns the option.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the all the customizable option prices for this option.
     */
    public function customizable_option_prices(): HasMany
    {
        return $this->hasMany(ProductCustomizableOptionPrice::class, 'product_customizable_option_id')
            ->orderBy('sort_order');
    }

    /**
     * Get id
     */
    #[ApiProperty(
        identifier: true,
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get type
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get label (translated attribute)
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getLabel(): ?string
    {
        // For TranslatableModel, label is stored in translations table
        // Try to get from current locale first
        $translation = $this->translations()->first();

        return $translation ? $translation->label : null;
    }

    /**
     * Get is_required
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getIs_required(): bool
    {
        return (bool) $this->is_required;
    }

    /**
     * Get max_characters
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getMax_characters(): ?int
    {
        return $this->max_characters;
    }

    /**
     * Get sort_order
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getSort_order(): ?int
    {
        return $this->sort_order;
    }

    /**
     * Get supported_file_extensions
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getSupported_file_extensions(): ?string
    {
        return $this->supported_file_extensions;
    }

    /**
     * Get the customizable option prices with explicit constraint
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['read'])]
    public function getCustomizable_option_prices()
    {
        // Fetch with explicit constraint to ensure we get only prices for this option
        return $this->customizable_option_prices()
            ->where('product_customizable_option_id', $this->id)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get translations
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['read'])]
    public function getTranslations()
    {
        return $this->translations;
    }
}
