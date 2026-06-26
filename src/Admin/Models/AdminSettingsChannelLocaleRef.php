<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Locale — nested sub-resource of AdminSettingsChannel (`locales` connection).
 * Backed by `locales` via a straight belongsToMany over `channel_locales`
 * (`channel_id`, `locale_id`) — the pivot has no own `id`, so the node `_id`
 * resolves to the related locale's real id. Surfaces `code`/`name`/`direction`.
 */
#[ApiResource(
    shortName: 'AdminSettingsChannelLocaleRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name', 'direction']],
)]
class AdminSettingsChannelLocaleRef extends Model
{
    /** @var string */
    protected $table = 'locales';

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
