<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Marketing event — shared nested sub-resource for Marketing resources that
 * reference an event (campaigns `marketingEvent` to-one, nullable). Backed by
 * `marketing_events`; used as a BelongsTo typed object. `name`/`date` are real
 * columns.
 */
#[ApiResource(
    shortName: 'AdminMarketingEventRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'name', 'date']],
)]
class AdminMarketingEventRef extends Model
{
    /** @var string */
    protected $table = 'marketing_events';

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
