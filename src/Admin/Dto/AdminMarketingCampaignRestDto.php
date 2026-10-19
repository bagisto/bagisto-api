<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminMarketingCampaignRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The subject.
     */
    #[ApiProperty(writable: false)]
    public ?string $subject = null;

    /**
     * The status.
     */
    #[ApiProperty(writable: false)]
    public ?int $status = null;

    /** @var array{id:int, code:string|null, name:string|null}|null */
    #[ApiProperty(writable: false)]
    public ?array $channel = null;

    /** @var array{id:int, code:string|null, name:string|null}|null */
    #[ApiProperty(writable: false)]
    public ?array $customer_group = null;

    /** @var array{id:int, name:string|null, status:string|null}|null */
    #[ApiProperty(writable: false)]
    public ?array $marketing_template = null;

    /** @var array{id:int, name:string|null, date:string|null}|null */
    #[ApiProperty(writable: false)]
    public ?array $marketing_event = null;

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
