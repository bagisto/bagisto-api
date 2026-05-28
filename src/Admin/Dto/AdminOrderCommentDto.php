<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminOrderCommentDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $orderId = null;

    public ?string $comment = null;

    public ?bool $customerNotified = null;

    public ?string $createdAt = null;

    public ?string $updatedAt = null;
}
