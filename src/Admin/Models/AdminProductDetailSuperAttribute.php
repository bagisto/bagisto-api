<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Configurable super-attribute — nested sub-resource of AdminCatalogProduct
 * (`superAttributes` connection). `options` is a nested connection.
 */
#[ApiResource(
    shortName: 'AdminProductDetailSuperAttribute',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'type', 'admin_name', 'options']],
)]
class AdminProductDetailSuperAttribute extends Model
{
    /** @var string */
    protected $table = 'attributes';

    /** @var array */
    protected $casts = ['id' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function options(): HasMany
    {
        return $this->hasMany(AdminProductDetailSuperAttributeOption::class, 'attribute_id')
            ->orderBy('sort_order');
    }
}
