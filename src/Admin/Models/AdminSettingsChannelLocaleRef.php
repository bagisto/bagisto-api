<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminSettingsChannelLocaleRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name', 'direction']],
)]
class AdminSettingsChannelLocaleRef extends Model
{
    /**
     * The table that stores the locales.
     *
     * @var string
     */
    protected $table = 'locales';

    /**
     * Indicates the channel locale reference does not maintain created and updated timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The native types the channel locale reference attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
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
