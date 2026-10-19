<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminMarketingSubscriberRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The email.
     */
    #[ApiProperty(writable: false)]
    public ?string $email = null;

    /**
     * The is subscribed.
     */
    #[ApiProperty(writable: false)]
    public ?bool $is_subscribed = null;

    /**
     * The customer id.
     */
    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    /**
     * The customer name.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    /** @var array{id:int, code:string|null, name:string|null}|null */
    #[ApiProperty(writable: false)]
    public ?array $channel = null;

    /**
     * The created at.
     */
    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
