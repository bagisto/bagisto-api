<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/campaigns/{id}/send and the
 * createAdminMarketingCampaignSend GraphQL mutation.
 *
 * The send action takes no body parameters — only the campaign id from
 * the URL (REST) or campaignId field (GraphQL).
 */
class AdminMarketingCampaignSendInput
{
    #[ApiProperty(description: 'Campaign id to send. REST takes it from the URL; GraphQL takes it from this field.')]
    #[Groups(['mutation'])]
    public ?int $campaignId = null;
}
