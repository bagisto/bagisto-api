<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSubscriberUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/subscribers/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The is subscribed.
     */
    #[ApiProperty(description: 'Subscription flag — true to subscribe, false to unsubscribe.')]
    #[Groups(['mutation'])]
    public ?bool $is_subscribed = null;
}
