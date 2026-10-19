<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCampaignSendInput
{
    /**
     * The campaign id.
     */
    #[ApiProperty(description: 'Campaign id to send. REST takes it from the URL; GraphQL takes it from this field.')]
    #[Groups(['mutation'])]
    public ?int $campaignId = null;
}
