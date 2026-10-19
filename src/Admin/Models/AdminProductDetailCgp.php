<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminProductDetailCgp',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'customer_group_id', 'qty', 'value_type', 'value', 'unique_id']],
)]
class AdminProductDetailCgp extends Model
{
    /** @var string */
    protected $table = 'product_customer_group_prices';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'customer_group_id' => 'int',
        'qty' => 'int',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get value.
     */
    #[ApiProperty(writable: false)]
    public function getValue(): ?string
    {
        return $this->attributes['value'] !== null ? (string) $this->attributes['value'] : null;
    }
}
