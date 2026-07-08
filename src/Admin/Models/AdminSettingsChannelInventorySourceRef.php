<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Inventory source — nested sub-resource of AdminSettingsChannel
 * (`inventorySources` connection). Backed by `inventory_sources` via
 * belongsToMany over `channel_inventory_sources` (`channel_id`,
 * `inventory_source_id`) — pivot has no own `id`, so the node `_id` is the
 * source's real id. Surfaces `code`/`name`/`status`.
 */
#[ApiResource(
    shortName: 'AdminSettingsChannelInventorySourceRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name', 'status']],
)]
class AdminSettingsChannelInventorySourceRef extends Model
{
    /** @var string */
    protected $table = 'inventory_sources';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'status' => 'int',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }
}
