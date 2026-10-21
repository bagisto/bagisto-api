<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminSettingsChannelInventorySourceRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name', 'status']],
)]
class AdminSettingsChannelInventorySourceRef extends Model
{
    /**
     * The table that stores the inventory sources.
     *
     * @var string
     */
    protected $table = 'inventory_sources';

    /**
     * Indicates the channel inventory source reference does not maintain created and updated timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The native types the channel inventory source reference attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'status' => 'int',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }
}
