<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[ApiResource(
    shortName: 'AdminCustomerReviewImage',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'path', 'url']],
)]
class AdminCustomerReviewImage extends Model
{
    protected $table = 'product_review_attachments';

    public $timestamps = false;

    protected $appends = ['url'];

    protected $casts = [
        'id' => 'int',
        'review_id' => 'int',
        'path' => 'string',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getPath(): ?string
    {
        return $this->path;
    }

    #[ApiProperty(writable: false)]
    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        try {
            return Storage::url($this->path);
        } catch (\Throwable $e) {
            return $this->path;
        }
    }
}
