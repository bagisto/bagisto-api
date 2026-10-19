<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCampaignCreateInput
{
    /**
     * The name.
     */
    #[ApiProperty(description: 'Campaign display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The subject.
     */
    #[ApiProperty(description: 'Email subject line.')]
    #[Groups(['mutation'])]
    public ?string $subject = null;

    /**
     * The marketing template id.
     */
    #[ApiProperty(description: 'FK marketing_templates.id (required).')]
    #[Groups(['mutation'])]
    public ?int $marketing_template_id = null;

    /**
     * The marketing event id.
     */
    #[ApiProperty(description: 'FK marketing_events.id. Required by monolith.')]
    #[Groups(['mutation'])]
    public ?int $marketing_event_id = null;

    /**
     * The channel id.
     */
    #[ApiProperty(description: 'FK channels.id (required).')]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    /**
     * The customer group id.
     */
    #[ApiProperty(description: 'FK customer_groups.id (required).')]
    #[Groups(['mutation'])]
    public ?int $customer_group_id = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Enabled flag (0/1). Defaults to 0 when omitted.')]
    #[Groups(['mutation'])]
    public ?int $status = null;
}
