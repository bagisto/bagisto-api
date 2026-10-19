<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminMarketingChannelRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name']],
)]
class AdminMarketingChannelRef extends Model
{
    /** @var string */
    protected $table = 'channels';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    protected $casts = [
        'id' => 'int',
    ];

    /** @var array */
    protected $appends = ['name'];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    /**
     * Get name attribute.
     */
    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        if ($this->id === null) {
            return null;
        }

        $locale = function_exists('app') ? app()->getLocale() : 'en';

        $query = DB::table('channel_translations')->where('channel_id', $this->id);

        $name = (clone $query)->where('locale', $locale)->value('name')
            ?: $query->value('name');

        return $name ?: $this->code;
    }
}
