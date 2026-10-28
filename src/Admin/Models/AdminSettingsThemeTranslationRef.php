<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Theme translation — nested sub-resource of AdminSettingsTheme (`translations` connection).
 */
#[ApiResource(
    shortName: 'AdminSettingsThemeTranslationRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'options']],
)]
class AdminSettingsThemeTranslationRef extends Model
{
    /** @var string */
    protected $table = 'theme_customization_translations';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'options' => 'array',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }
}
