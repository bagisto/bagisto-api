<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCustomerReviewListDto
{
    /**
     * The id.
     */
    public ?int $id = null;

    /**
     * The title.
     */
    public ?string $title = null;

    /**
     * The comment.
     */
    public ?string $comment = null;

    /**
     * The rating.
     */
    public ?int $rating = null;

    /**
     * The status.
     */
    public ?string $status = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;

    /**
     * The product.
     */
    public ?array $product = null;

    /**
     * The customer.
     */
    public ?array $customer = null;
}
