<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminBookingProductConfigResolver;
use Webkul\BagistoApi\Admin\State\AdminBookingProductProvider;

/**
 * Booking configuration the admin Create Order drawer needs to add a booking product to a draft
 * cart: sub-type, availability window, calendar and the type-specific rental, table or ticket data.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminBookingProduct',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/booking-products/{productId}/config',
            provider: AdminBookingProductProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Booking product configuration for Create Order',
                description: 'Returns what the admin Create Order booking drawer shows for a booking product. Slots for a date come from `GET /booking-products/{productId}/slots?date=`.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Booking configuration.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'productId' => 12,
                                    'bookingProductId' => 3,
                                    'type' => 'event',
                                    'availableEveryWeek' => false,
                                    'availableFrom' => '2026-10-01',
                                    'availableTo' => '2026-12-31',
                                    'calendar' => [
                                        'valid_weekdays' => [0, 1, 2, 3, 4, 5, 6],
                                        'available_every_week' => false,
                                        'available_from' => '2026-10-01',
                                        'available_to' => '2026-12-31',
                                        'prevent_scheduling_before' => 0,
                                        'disabled_dates' => [],
                                    ],
                                    'rentalSlot' => null,
                                    'tableSlot' => null,
                                    'eventTickets' => [[
                                        'id' => 1,
                                        'price' => '20.0000',
                                        'qty' => 100,
                                        'name' => 'Standard',
                                        'formatted_price' => '$20.00',
                                        'formatted_total_price' => '$20.00',
                                    ]],
                                ]],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Not a booking product.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'config',
            resolver: AdminBookingProductConfigResolver::class,
            args: ['productId' => ['type' => 'Int!']],
            normalizationContext: ['groups' => ['query']],
            description: 'Booking configuration for the admin Create Order drawer.',
        ),
    ],
)]
class AdminBookingProduct
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?int $product_id = null;

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?int $booking_product_id = null;

    #[ApiProperty(writable: false, description: 'default, appointment, event, rental or table.')]
    #[Groups(['query'])]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?bool $available_every_week = null;

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?string $available_from = null;

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?string $available_to = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(writable: false, description: 'Valid weekdays, availability window and disabled dates for the date picker.')]
    #[Groups(['query'])]
    public ?array $calendar = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(writable: false, description: 'Rental products only: renting type and daily or hourly price.')]
    #[Groups(['query'])]
    public ?array $rental_slot = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(writable: false, description: 'Table products only: guest capacity, guest limit and price type.')]
    #[Groups(['query'])]
    public ?array $table_slot = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ApiProperty(writable: false, description: 'Event products only: the tickets with their prices.')]
    #[Groups(['query'])]
    public ?array $event_tickets = null;
}
