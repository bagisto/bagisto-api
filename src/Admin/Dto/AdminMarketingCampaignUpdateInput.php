<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/marketing/campaigns/{id} and the delete mutation.
 */
class AdminMarketingCampaignUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/campaigns/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $subject = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $marketing_template_id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $marketing_event_id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $customer_group_id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;
}
