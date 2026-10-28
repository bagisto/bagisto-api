<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminCustomerGroupRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name']],
)]
class AdminCustomerGroupRef extends Model
{
    /**
     * The table.
     */
    protected $table = 'customer_groups';

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
     * Get code.
     */
    #[ApiProperty(writable: false)]
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Get name.
     */
    #[ApiProperty(writable: false)]
    public function getName(): ?string
    {
        return $this->name;
    }
}
