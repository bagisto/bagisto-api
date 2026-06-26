<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer group — shared nested sub-resource for Marketing resources that
 * reference customer groups (catalog rules / cart rules `customerGroups`
 * connections; campaigns `customerGroup` to-one). Backed by `customer_groups`.
 *
 * Used as a belongsToMany node (pivot has no own id → node `_id` is the group's
 * real id) and as a BelongsTo typed object. `code`/`name` are real columns.
 */
#[ApiResource(
    shortName: 'AdminMarketingCustomerGroupRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name']],
)]
class AdminMarketingCustomerGroupRef extends Model
{
    /** @var string */
    protected $table = 'customer_groups';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    protected $casts = [
        'id' => 'int',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }
}
