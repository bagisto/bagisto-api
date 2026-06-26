<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCustomerReviewDetailDto
{
    public ?int $id = null;

    public ?string $title = null;

    public ?string $comment = null;

    public ?int $rating = null;

    public ?string $status = null;

    public ?string $name = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    public ?array $product = null;

    public ?array $customer = null;

    public array $images = [];
}
