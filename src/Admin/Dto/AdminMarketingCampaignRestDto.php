<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminMarketingCampaign. Snake_case props surface as camelCase
 * via the central converter.
 *
 * The former bare FK scalars (channelId / customerGroupId / marketingTemplateId
 * / marketingEventId) and the resolved flat names (channelName /
 * customerGroupCode / marketingTemplateName / marketingEventName) are REPLACED by
 * four to-one objects: channel {id,code,name}, customer_group {id,code,name},
 * marketing_template {id,name,status}, marketing_event {id,name,date}|null.
 *
 * Name-match trap: each object prop must be named `channel` / `customer_group` /
 * `marketing_template` / `marketing_event` to match the parent Eloquent
 * resource's belongsTo relations.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminMarketingCampaignRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $subject = null;

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

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
