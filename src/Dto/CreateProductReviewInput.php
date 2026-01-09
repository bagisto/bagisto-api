<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO for creating product reviews via GraphQL mutation
 * This explicitly defines all input fields for the GraphQL schema
 */
#[ApiResource]
class CreateProductReviewInput
{
    #[Groups(['mutation'])]
    public int $productId;

    #[Groups(['mutation'])]
    public string $title;

    #[Groups(['mutation'])]
    public string $comment;

    #[Groups(['mutation'])]
    public int $rating;

    #[Groups(['mutation'])]
    public string $name;

    #[Groups(['mutation'])]
    public ?string $email = null;

    #[Groups(['mutation'])]
    public ?int $status = null;

    #[Groups(['mutation'])]
    public ?string $attachments;

    public function __construct(
        int $productId,
        string $title,
        string $comment,
        int $rating,
        string $name,
        ?string $email = null,
        ?int $status = null,
        ?string $attachments = '',
    ) {
        $this->productId = $productId;
        $this->title = $title;
        $this->comment = $comment;
        $this->rating = $rating;
        $this->name = $name;
        $this->email = $email;
        $this->status = $status;
        $this->attachments = $attachments;
    }
}
