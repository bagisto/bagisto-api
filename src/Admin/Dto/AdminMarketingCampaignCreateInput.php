<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/campaigns + createAdminMarketingCampaign.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\CampaignController::store
 * validation rules.
 */
class AdminMarketingCampaignCreateInput
{
    #[ApiProperty(description: 'Campaign display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Email subject line.')]
    #[Groups(['mutation'])]
    public ?string $subject = null;

    #[ApiProperty(description: 'FK marketing_templates.id (required).')]
    #[Groups(['mutation'])]
    public ?int $marketing_template_id = null;

    #[ApiProperty(description: 'FK marketing_events.id. Required by monolith.')]
    #[Groups(['mutation'])]
    public ?int $marketing_event_id = null;

    #[ApiProperty(description: 'FK channels.id (required).')]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    #[ApiProperty(description: 'FK customer_groups.id (required).')]
    #[Groups(['mutation'])]
    public ?int $customer_group_id = null;

    #[ApiProperty(description: 'Enabled flag (0/1). Defaults to 0 when omitted.')]
    #[Groups(['mutation'])]
    public ?int $status = null;
}
