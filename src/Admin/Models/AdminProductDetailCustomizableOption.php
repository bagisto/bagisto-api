<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Customizable option — nested sub-resource of AdminCatalogProduct
 * (`customizableOptions` connection). `translations` + `prices` are nested
 * connections.
 */
#[ApiResource(
    shortName: 'AdminProductDetailCustomizableOption',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => [
        'id', 'type', 'is_required', 'sort_order', 'max_characters',
        'supported_file_extensions', 'translations', 'prices',
    ]],
)]
class AdminProductDetailCustomizableOption extends Model
{
    /** @var string */
    protected $table = 'product_customizable_options';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'is_required' => 'boolean',
        'sort_order' => 'int',
        'max_characters' => 'int',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function translations(): HasMany
    {
        return $this->hasMany(AdminProductDetailCustomizableOptionTranslation::class, 'product_customizable_option_id');
    }

    #[ApiProperty(writable: false)]
    public function prices(): HasMany
    {
        return $this->hasMany(AdminProductDetailCustomizableOptionPrice::class, 'product_customizable_option_id')
            ->orderBy('sort_order');
    }
}
