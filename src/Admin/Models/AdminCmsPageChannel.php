<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminCmsPageChannel',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name']],
)]
class AdminCmsPageChannel extends Model
{
    protected $table = 'channels';

    protected $casts = [
        'id' => 'int',
    ];

    protected $appends = ['name'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getCodeAttribute($value): ?string
    {
        return $value;
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        if (array_key_exists('name', $this->attributes)) {
            return $this->attributes['name'];
        }

        $translation = DB::table('channel_translations')
            ->where('channel_id', $this->id)
            ->where('locale', app()->getLocale())
            ->value('name');

        if ($translation === null) {
            $translation = DB::table('channel_translations')
                ->where('channel_id', $this->id)
                ->value('name');
        }

        return $translation;
    }
}
