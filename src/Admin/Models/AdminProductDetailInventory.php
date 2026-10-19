<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminProductDetailInventory',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'source_id', 'source_code', 'qty']],
)]
class AdminProductDetailInventory extends Model
{
    /** @var string */
    protected $table = 'product_inventories';

    /** @var array */
    protected $appends = ['source_id', 'source_code'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
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
     * Get source id attribute.
     */
    #[ApiProperty(writable: false)]
    public function getSourceIdAttribute(): ?int
    {
        return $this->inventory_source_id !== null ? (int) $this->inventory_source_id : null;
    }

    /**
     * Get source code attribute.
     */
    #[ApiProperty(writable: false)]
    public function getSourceCodeAttribute(): ?string
    {
        if (! $this->inventory_source_id) {
            return null;
        }

        return DB::table('inventory_sources')->where('id', $this->inventory_source_id)->value('code');
    }
}
