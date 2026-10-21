<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminSettingsChannelTranslationRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'name', 'description', 'maintenance_mode_text', 'home_seo']],
)]
class AdminSettingsChannelTranslationRef extends Model
{
    /**
     * The table that stores the channel translations.
     *
     * @var string
     */
    protected $table = 'channel_translations';

    /**
     * Indicates the channel translation does not maintain created and updated timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The native types the channel translation attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'home_seo' => 'array',
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
