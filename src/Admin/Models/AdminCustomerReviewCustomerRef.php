<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminCustomerReviewCustomerRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'name', 'email']],
)]
class AdminCustomerReviewCustomerRef extends Model
{
    /**
     * The table.
     */
    protected $table = 'customers';

    /**
     * The secret customer columns that are never serialized.
     */
    protected $hidden = ['password', 'api_token', 'remember_token', 'token'];

    /**
     * The appends.
     */
    protected $appends = ['name'];

    /**
     * The casts.
     */
    protected $casts = ['id' => 'int'];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get email.
     */
    #[ApiProperty(writable: false)]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get name attribute.
     */
    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return trim((string) ($this->first_name ?? '').' '.(string) ($this->last_name ?? '')) ?: null;
    }
}
