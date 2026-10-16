<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for marking an order notification as read.
 */
class AdminNotificationReadInput
{
    #[ApiProperty(description: 'Order id the notification belongs to.')]
    #[Groups(['mutation'])]
    public ?int $order_id = null;
}
