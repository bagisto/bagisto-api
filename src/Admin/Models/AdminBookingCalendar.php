<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminBookingCalendarResolver;
use Webkul\BagistoApi\Admin\State\AdminBookingCalendarProvider;

/**
 * Bookings overlapping a date window, as the admin Sales → Bookings calendar view shows them.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminBookingCalendar',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/bookings/calendar',
            provider: AdminBookingCalendarProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Bookings'],
                summary: 'Bookings calendar',
                description: 'Returns every booking whose slot overlaps the window, unpaginated, the way the admin bookings calendar view loads them. The window defaults to the current week. Slot times are formatted in the store timezone and each booking carries the attributes stored on its order item.',
                parameters: [
                    new Model\Parameter('start', 'query', 'Window start (Y-m-d). Defaults to the start of the current week.', false, schema: ['type' => 'string', 'format' => 'date', 'example' => '2026-10-12']),
                    new Model\Parameter('end', 'query', 'Window end (Y-m-d), inclusive. Defaults to the end of the current week.', false, schema: ['type' => 'string', 'format' => 'date', 'example' => '2026-10-18']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The bookings in the window.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'id' => 'bookings-calendar',
                                    'start' => '2026-10-12',
                                    'end' => '2026-10-18',
                                    'bookings' => [[
                                        'id' => 4,
                                        'orderId' => 31,
                                        'start' => '2026-10-14 10:00 AM',
                                        'end' => '2026-10-14 11:00 AM',
                                        'status' => 'processing',
                                        'email' => 'john@example.com',
                                        'fullName' => 'John Doe',
                                        'createdAt' => '2026-10-10 09:12:44',
                                        'productSku' => 'BK-APPOINTMENT-01',
                                        'productName' => 'Doctor Appointment',
                                        'address' => '12 Main Street',
                                        'contact' => '9876543210',
                                        'city' => 'Austin',
                                        'state' => 'TX',
                                        'country' => 'US',
                                        'postcode' => '73301',
                                        'attributes' => [
                                            ['attribute_name' => 'Booking From', 'option_label' => '14 Oct, 2026 10:00 AM'],
                                            ['attribute_name' => 'Booking Till', 'option_label' => '14 Oct, 2026 11:00 AM'],
                                        ],
                                    ]],
                                ]],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.bookings.'),
                    '422' => new Model\Response(description: 'start or end is not a Y-m-d date.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'get',
            resolver: AdminBookingCalendarResolver::class,
            args: [
                'start' => ['type' => 'String'],
                'end' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query'], 'skip_null_values' => false],
            description: 'Bookings overlapping a date window for the admin bookings calendar.',
        ),
    ],
)]
class AdminBookingCalendar
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?string $id = 'bookings-calendar';

    #[ApiProperty(writable: false, description: 'Window start (Y-m-d).')]
    #[Groups(['query'])]
    public ?string $start = null;

    #[ApiProperty(writable: false, description: 'Window end (Y-m-d).')]
    #[Groups(['query'])]
    public ?string $end = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false, description: 'Bookings overlapping the window.')]
    #[Groups(['query'])]
    public array $bookings = [];
}
