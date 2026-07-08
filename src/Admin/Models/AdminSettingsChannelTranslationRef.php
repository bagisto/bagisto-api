<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Channel translation — nested sub-resource of AdminSettingsChannel
 * (`translations` connection). Backed by `channel_translations` as a plain
 * HasMany (standard FK `channel_id` → no pivot gotcha).
 *
 * `home_seo` is a real JSON column → it resolves over GraphQL as a JSON value
 * on the node (real cast columns are fine; only computed array ACCESSORS 500).
 * `locale`/`name`/`description`/`maintenance_mode_text` are real columns too.
 * The row id surfaces as `_id`; `maintenance_mode_text`/`home_seo` surface as
 * `maintenanceModeText`/`homeSeo` via the central converter.
 */
#[ApiResource(
    shortName: 'AdminSettingsChannelTranslationRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'name', 'description', 'maintenance_mode_text', 'home_seo']],
)]
class AdminSettingsChannelTranslationRef extends Model
{
    /** @var string */
    protected $table = 'channel_translations';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'home_seo' => 'array',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }
}
