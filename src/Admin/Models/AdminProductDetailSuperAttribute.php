<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    shortName: 'AdminProductDetailSuperAttribute',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'type', 'admin_name', 'options']],
)]
class AdminProductDetailSuperAttribute extends Model
{
    /**
     * The table that stores the attributes.
     *
     * @var string
     */
    protected $table = 'attributes';

    /**
     * The native types the configurable (super) attribute attributes are cast to.
     *
     * @var array
     */
    protected $casts = ['id' => 'int'];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Options.
     */
    #[ApiProperty(writable: false)]
    public function options(): HasMany
    {
        return $this->hasMany(AdminProductDetailSuperAttributeOption::class, 'attribute_id')
            ->orderBy('sort_order');
    }
}
