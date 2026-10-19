<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCampaignUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/campaigns/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The subject.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $subject = null;

    /**
     * The marketing template id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $marketing_template_id = null;

    /**
     * The marketing event id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $marketing_event_id = null;

    /**
     * The channel id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    /**
     * The customer group id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $customer_group_id = null;

    /**
     * The status.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;
}
