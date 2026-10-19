<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminOrderCommentCreateInput
{
    /**
     * The order id.
     */
    #[ApiProperty(description: 'Order id (only used over GraphQL — REST takes it from the URL).')]
    #[Groups(['mutation'])]
    public ?int $orderId = null;

    /**
     * The comment.
     */
    #[ApiProperty(description: 'Comment body.')]
    #[Groups(['mutation'])]
    public ?string $comment = null;

    /**
     * The customer notified.
     */
    #[ApiProperty(description: 'When true the customer is notified by email (Bagisto core listener).')]
    #[Groups(['mutation'])]
    public ?bool $customerNotified = null;
}
