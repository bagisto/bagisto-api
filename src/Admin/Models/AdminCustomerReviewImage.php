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
    /**
     * The table.
     */
    protected $table = 'product_review_attachments';

    /**
     * The timestamps.
     */
    public $timestamps = false;

    /**
     * The appends.
     */
    protected $appends = ['url'];

    /**
     * The casts.
     */
    protected $casts = [
        'id' => 'int',
        'review_id' => 'int',
        'path' => 'string',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get path.
     */
    #[ApiProperty(writable: false)]
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Get url attribute.
     */
    #[ApiProperty(writable: false)]
    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        try {
            return Storage::url($this->path);
        } catch (\Throwable $exception) {
            return $this->path;
        }
    }
}
