<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
class CreateProductReviewInput
{
    /**
     * The product id.
     */
    #[Groups(['mutation'])]
    public int $productId;

    /**
     * The title.
     */
    #[Groups(['mutation'])]
    public string $title;

    /**
     * The comment.
     */
    #[Groups(['mutation'])]
    public string $comment;

    /**
     * The rating.
     */
    #[Groups(['mutation'])]
    public int $rating;

    /**
     * The name.
     */
    #[Groups(['mutation'])]
    public string $name;

    /**
     * The email.
     */
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * The status.
     */
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The attachments.
     */
    #[Groups(['mutation'])]
    public ?string $attachments;

    /**
     * Create a new instance.
     */
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
