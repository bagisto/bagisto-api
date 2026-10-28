<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for POST /api/admin/carts/{id}/items and the GraphQL
 * addItemAdminCart mutation.
 *
 * REST reads the raw request body, so it also accepts the storefront snake_case
 * keys directly (`selected_configurable_option`, `bundle_options`,
 * `bundle_option_qty`, `qty`, `links`). GraphQL can only send the fields
 * declared here, so the type-specific selections are exposed as GraphQL-friendly
 * scalars / lists (no arbitrary-key maps — those don't round-trip over GraphQL)
 * and the processor transforms them into the map shape Cart::addProduct expects:
 *
 *   - configurable : selectedConfigurableOption (variant product id)
 *   - downloadable : links (list of downloadable-link ids)
 *   - grouped      : groupedQuantities ([{ productId, quantity }])
 *   - bundle       : bundleOptions ([{ optionId, productIds, quantity }])
 *   - booking      : booking ({ type, date, slot, ... }), passed to the cart as sent
 */
class AdminCartAddItemInput
{
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    #[Groups(['mutation'])]
    public ?int $productId = null;

    #[Groups(['mutation'])]
    public ?int $quantity = null;

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

    /**
     * Booking products: the drawer selection, e.g. { type, date, slot } or event { type, qty }.
     *
     * @var array<string, mixed>|null
     */
    #[ApiProperty(description: 'Booking products: { type, date, slot } for default, appointment and table; { type, date_from, date_to } or { type, date, slot } for rental; { type, qty: { ticketId: qty } } for event. Table bookings also take note.')]
    #[Groups(['mutation'])]
    public ?array $booking = null;
}
