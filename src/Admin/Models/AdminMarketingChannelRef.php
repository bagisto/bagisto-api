<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Channel — shared nested sub-resource for Marketing resources that reference
 * channels (catalog rules / cart rules `channels` connections; campaigns /
 * subscribers / search terms `channel` to-one). Backed by `channels`.
 *
 * Used as a belongsToMany node (pivot has no own id → node `_id` is the
 * channel's real id) and as a BelongsTo typed object.
 *
 * `name` is computed from `channel_translations` (channels has no name column);
 * it is a STRING accessor (safe over GraphQL) recomputed from the node's own
 * `id`, so it survives connection re-resolution.
 */
#[ApiResource(
    shortName: 'AdminMarketingChannelRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name']],
)]
class AdminMarketingChannelRef extends Model
{
    /** @var string */
    protected $table = 'channels';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    protected $casts = [
        'id' => 'int',
    ];

    /** @var array */
    protected $appends = ['name'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        if ($this->id === null) {
            return null;
        }

        $locale = function_exists('app') ? app()->getLocale() : 'en';

        $query = DB::table('channel_translations')->where('channel_id', $this->id);

        $name = (clone $query)->where('locale', $locale)->value('name')
            ?: $query->value('name');

        return $name ?: $this->code;
    }
}
