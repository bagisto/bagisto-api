<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCartAddItemInput
{
    /**
     * The cart id.
     */
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    /**
     * The product id.
     */
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * The quantity.
     */
    #[Groups(['mutation'])]
    public ?int $quantity = null;

    /**
     * The selected configurable option.
     */
    #[ApiProperty(description: 'Configurable products: the chosen variant product id.')]
    #[Groups(['mutation'])]
    public ?int $selectedConfigurableOption = null;

    /**
     * Downloadable products: ids of the downloadable links to purchase.
     *
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'Downloadable products: list of downloadable-link ids.')]
    #[Groups(['mutation'])]
    public ?array $links = null;

    /**
     * Grouped products: per associated-product quantities.
     *
     * @var array<int, array{productId: int, quantity: int}>|null
     */
    #[ApiProperty(description: 'Grouped products: list of { productId, quantity }.')]
    #[Groups(['mutation'])]
    public ?array $groupedQuantities = null;

    /**
     * Bundle products: chosen option selections.
     *
     * @var array<int, array{optionId: int, productIds: array<int, int>, quantity?: int}>|null
     */
    #[ApiProperty(description: 'Bundle products: list of { optionId, productIds, quantity }.')]
    #[Groups(['mutation'])]
    public ?array $bundleOptions = null;
}
