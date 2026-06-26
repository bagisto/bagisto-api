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
    protected $table = 'customers';

    protected $appends = ['name'];

    protected $casts = ['id' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return trim((string) ($this->first_name ?? '').' '.(string) ($this->last_name ?? '')) ?: null;
    }
}
