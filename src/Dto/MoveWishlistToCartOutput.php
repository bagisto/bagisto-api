<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class MoveWishlistToCartOutput
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The success.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Whether the operation was successful')]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Message describing the result')]
    public ?string $message = null;

    /**
     * The wishlist item id.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'ID of the wishlist item that was moved')]
    public ?int $wishlistItemId = null;
}
