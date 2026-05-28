<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminOrderCommentCreateInput
{
    #[ApiProperty(description: 'Order id (only used over GraphQL — REST takes it from the URL).')]
    #[Groups(['mutation'])]
    public ?int $orderId = null;

    #[ApiProperty(description: 'Comment body.')]
    #[Groups(['mutation'])]
    public ?string $comment = null;

    #[ApiProperty(description: 'When true the customer is notified by email (Bagisto core listener).')]
    #[Groups(['mutation'])]
    public ?bool $customerNotified = null;
}
