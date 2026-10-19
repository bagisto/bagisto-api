<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class UpdateProductReviewInput
{
    /**
     * The id.
     */
    #[Groups(['mutation'])]
    public string $id;

    /**
     * The product id.
     */
    #[Groups(['mutation'])]
    #[SerializedName('productId')]
    public ?int $product_id = null;

    /**
     * The title.
     */
    #[Groups(['mutation'])]
    public ?string $title = null;

    /**
     * The comment.
     */
    #[Groups(['mutation'])]
    public ?string $comment = null;

    /**
     * The rating.
     */
    #[Groups(['mutation'])]
    public ?int $rating = null;

    /**
     * The name.
     */
    #[Groups(['mutation'])]
    public ?string $name = null;

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
     * Create a new instance.
     */
    public function __construct(
        string $id = '',
        ?int $product_id = null,
        ?string $title = null,
        ?string $comment = null,
        ?int $rating = null,
        ?string $name = null,
        ?string $email = null,
        ?int $status = null,
    ) {
        $this->id = $id;
        $this->product_id = $product_id;
        $this->title = $title;
        $this->comment = $comment;
        $this->rating = $rating;
        $this->name = $name;
        $this->email = $email;
        $this->status = $status;
    }
}
