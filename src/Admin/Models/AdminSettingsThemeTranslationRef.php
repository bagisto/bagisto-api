<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminSettingsThemeTranslationRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'options']],
)]
class AdminSettingsThemeTranslationRef extends Model
{
    /**
     * The table that stores the theme customization translations.
     *
     * @var string
     */
    protected $table = 'theme_customization_translations';

    /**
     * Indicates the theme customization translation does not maintain created and updated timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The native types the theme customization translation attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'options' => 'array',
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
