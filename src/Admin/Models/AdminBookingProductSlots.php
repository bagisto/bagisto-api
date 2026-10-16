<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminBookingProductSlotsResolver;
use Webkul\BagistoApi\Admin\State\AdminBookingProductProvider;

/**
 * Bookable slots of a booking product on one date, for the admin Create Order drawer.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminBookingProductSlots',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/booking-products/{productId}/slots',
            provider: AdminBookingProductProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Booking slots for a date',
                description: 'Returns the slots a booking product offers on the given date. Send the chosen slot back as `booking.slot` when adding the product to a draft cart.',
                parameters: [
                    new Model\Parameter('date', 'query', 'Required. Date in `Y-m-d` format.', true, schema: ['type' => 'string', 'format' => 'date']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Slots for the date.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'productId' => 14,
                                    'date' => '2026-10-20',
                                    'slots' => [[
                                        'from' => '09:00 AM',
                                        'to' => '10:00 AM',
                                        'timestamp' => '1792472400-1792476000',
                                        'qty' => 10,
                                    ]],
                                ]],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Not a booking product.'),
                    '422' => new Model\Response(description: 'Missing or invalid date.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: AdminBookingProductSlotsResolver::class,
            args: [
                'productId' => ['type' => 'Int!'],
                'date' => ['type' => 'String!'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Bookable slots of a booking product on one date.',
        ),
    ],
)]
class AdminBookingProductSlots
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?int $product_id = null;

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?string $date = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?array $slots = null;
}
