<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminMarketingTemplateRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'name', 'status']],
)]
class AdminMarketingTemplateRef extends Model
{
    /** @var string */
    protected $table = 'marketing_templates';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
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
