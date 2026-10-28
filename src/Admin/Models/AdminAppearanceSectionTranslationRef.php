<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminAppearanceSectionTranslation',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['locale', 'options', 'draft_options']],
)]
class AdminAppearanceSectionTranslationRef extends Model
{
    protected $table = 'theme_section_translations';

    public $timestamps = false;

    protected $casts = [
        'id' => 'int',
        'section_id' => 'int',
        'options' => 'array',
        'draft_options' => 'array',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }
}
