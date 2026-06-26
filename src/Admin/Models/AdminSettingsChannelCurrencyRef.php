<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Currency — nested sub-resource of AdminSettingsChannel (`currencies`
 * connection). Backed by `currencies` via belongsToMany over `channel_currencies`
 * (`channel_id`, `currency_id`) — pivot has no own `id`, so the node `_id` is the
 * currency's real id. Surfaces `code`/`name`/`symbol`.
 */
#[ApiResource(
    shortName: 'AdminSettingsChannelCurrencyRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name', 'symbol']],
)]
class AdminSettingsChannelCurrencyRef extends Model
{
    /** @var string */
    protected $table = 'currencies';

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
