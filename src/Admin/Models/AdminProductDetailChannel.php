<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminProductDetailChannel',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name']],
)]
class AdminProductDetailChannel extends Model
{
    protected $table = 'channels';

    protected $appends = ['name'];

    protected $casts = ['id' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return DB::table('channel_translations')->where('channel_id', $this->id)
            ->orderByRaw('locale = ? desc', [app()->getLocale()])
            ->value('name');
    }
}
