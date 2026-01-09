<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * DTO for updating product reviews via GraphQL mutation
 * The 'id' field accepts IRI format like "/api/shop/reviews/16"
 */
class UpdateProductReviewInput
{
    #[Groups(['mutation'])]
    public string $id;

    #[Groups(['mutation'])]
    #[SerializedName('productId')]
    public ?int $product_id = null;

    #[Groups(['mutation'])]
    public ?string $title = null;

    #[Groups(['mutation'])]
    public ?string $comment = null;

    #[Groups(['mutation'])]
    public ?int $rating = null;

    #[Groups(['mutation'])]
    public ?string $name = null;

    #[Groups(['mutation'])]
    public ?string $email = null;

    #[Groups(['mutation'])]
    public ?int $status = null;

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
