<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\BookingProduct\Models\BookingProductAppointmentSlot;
use Webkul\BookingProduct\Models\BookingProductDefaultSlot;
use Webkul\BookingProduct\Models\BookingProductEventTicket;
use Webkul\BookingProduct\Models\BookingProductRentalSlot;
use Webkul\BookingProduct\Models\BookingProductTableSlot;

class ProductBookingQueryTest extends GraphQLTestCase
{
    /**
     * Assert common product fields returned by the booking product query.
     */
    private function assertProductFields(array $data): void
    {
        $this->assertNotNull($data, 'product response is null');
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('sku', $data);
        $this->assertArrayHasKey('urlKey', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertNotNull($data['name']);
    }

    /**
     * Extract the first booking product node from the response data.
     */
    private function extractBookingNode(array $data, string $expectedType): array
    {
        $this->assertArrayHasKey('bookingProducts', $data);
        $edges = $data['bookingProducts']['edges'] ?? [];
        $this->assertNotEmpty($edges, 'bookingProducts.edges should not be empty');

        $node = $edges[0]['node'] ?? null;
        $this->assertNotNull($node, 'bookingProducts first node is null');
        $this->assertArrayHasKey('_id', $node);
        $this->assertSame($expectedType, $node['type'] ?? '');

        return $node;
    }

    // ─── Appointment ────────────────────────────────────────────────────

    /**
     * Test querying appointment booking product with full slot details.
     */
    public function test_get_appointment_booking_product(): void
    {
        $bookingData = $this->createBookingProductFixture('appointment');

        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                sku
                urlKey
                price
                bookingProducts {
                  edges {
                    node {
                      _id
                      type
                      appointmentSlot {
                        id
                        _id
                        bookingProductId
                        duration
                        breakTime
                        sameSlotAllDays
                        slots
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'id' => (string) $bookingData['product']->id,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.product');
        $this->assertProductFields($data);

        $node = $this->extractBookingNode($data, 'appointment');

        // Appointment slot assertions
        $slot = $node['appointmentSlot'] ?? null;
        $this->assertNotNull($slot, 'appointmentSlot should not be null');
        $this->assertArrayHasKey('id', $slot);
        $this->assertArrayHasKey('_id', $slot);
        $this->assertArrayHasKey('bookingProductId', $slot);
        $this->assertSame((int) $bookingData['booking']->id, (int) $slot['bookingProductId']);
        $this->assertArrayHasKey('duration', $slot);
        $this->assertSame(30, (int) $slot['duration']);
        $this->assertArrayHasKey('breakTime', $slot);
        $this->assertSame(0, (int) $slot['breakTime']);
        $this->assertArrayHasKey('sameSlotAllDays', $slot);
        $this->assertArrayHasKey('slots', $slot);
        $this->assertNotNull($slot['slots']);
    }

    // ─── Rental ─────────────────────────────────────────────────────────

    /**
     * Test querying rental booking product with full slot details.
     */
    public function test_get_rental_booking_product(): void
    {
        $bookingData = $this->createBookingProductFixture('rental');

        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                sku
                urlKey
                price
                bookingProducts {
                  edges {
                    node {
                      _id
                      type
                      availableFrom
                      availableTo
                      rentalSlot {
                        id
                        _id
                        bookingProductId
                        rentingType
                        dailyPrice
                        hourlyPrice
                        sameSlotAllDays
                        slots
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'id' => (string) $bookingData['product']->id,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.product');
        $this->assertProductFields($data);

        $node = $this->extractBookingNode($data, 'rental');

        // availableFrom/availableTo present (nullable for rental)
        $this->assertArrayHasKey('availableFrom', $node);
        $this->assertArrayHasKey('availableTo', $node);

        // Rental slot assertions
        $slot = $node['rentalSlot'] ?? null;
        $this->assertNotNull($slot, 'rentalSlot should not be null');
        $this->assertArrayHasKey('id', $slot);
        $this->assertArrayHasKey('_id', $slot);
        $this->assertArrayHasKey('bookingProductId', $slot);
        $this->assertSame((int) $bookingData['booking']->id, (int) $slot['bookingProductId']);
        $this->assertArrayHasKey('rentingType', $slot);
        $this->assertSame('daily', $slot['rentingType']);
        $this->assertArrayHasKey('dailyPrice', $slot);
        $this->assertSame(10.0, (float) $slot['dailyPrice']);
        $this->assertArrayHasKey('hourlyPrice', $slot);
        $this->assertArrayHasKey('sameSlotAllDays', $slot);
        $this->assertArrayHasKey('slots', $slot);
    }

    // ─── Default ────────────────────────────────────────────────────────

    /**
     * Test querying default booking product with full slot details.
     */
    public function test_get_default_booking_product(): void
    {
        $bookingData = $this->createBookingProductFixture('default');

        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                sku
                urlKey
                price
                bookingProducts {
                  edges {
                    node {
                      _id
                      type
                      defaultSlot {
                        id
                        _id
                        bookingType
                        duration
                        breakTime
                        slots
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'id' => (string) $bookingData['product']->id,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.product');
        $this->assertProductFields($data);

        $node = $this->extractBookingNode($data, 'default');

        // Default slot assertions
        $slot = $node['defaultSlot'] ?? null;
        $this->assertNotNull($slot, 'defaultSlot should not be null');
        $this->assertArrayHasKey('id', $slot);
        $this->assertArrayHasKey('_id', $slot);
        $this->assertArrayHasKey('bookingType', $slot);
        $this->assertSame('many', $slot['bookingType']);
        $this->assertArrayHasKey('duration', $slot);
        $this->assertSame(30, (int) $slot['duration']);
        $this->assertArrayHasKey('breakTime', $slot);
        $this->assertSame(0, (int) $slot['breakTime']);
        $this->assertArrayHasKey('slots', $slot);
        $this->assertNotNull($slot['slots']);
    }

    // ─── Table ──────────────────────────────────────────────────────────

    /**
     * Test querying table booking product with full slot details.
     */
    public function test_get_table_booking_product(): void
    {
        $bookingData = $this->createBookingProductFixture('table');

        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                sku
                urlKey
                price
                bookingProducts {
                  edges {
                    node {
                      _id
                      type
                      availableFrom
                      availableTo
                      tableSlot {
                        id
                        _id
                        bookingProductId
                        priceType
                        guestLimit
                        duration
                        breakTime
                        preventSchedulingBefore
                        sameSlotAllDays
                        slots
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'id' => (string) $bookingData['product']->id,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.product');
        $this->assertProductFields($data);

        $node = $this->extractBookingNode($data, 'table');

        $this->assertArrayHasKey('availableFrom', $node);
        $this->assertArrayHasKey('availableTo', $node);

        // Table slot assertions
        $slot = $node['tableSlot'] ?? null;
        $this->assertNotNull($slot, 'tableSlot should not be null');
        $this->assertArrayHasKey('id', $slot);
        $this->assertArrayHasKey('_id', $slot);
        $this->assertArrayHasKey('bookingProductId', $slot);
        $this->assertSame((int) $bookingData['booking']->id, (int) $slot['bookingProductId']);
        $this->assertArrayHasKey('priceType', $slot);
        $this->assertSame('table', $slot['priceType']);
        $this->assertArrayHasKey('guestLimit', $slot);
        $this->assertSame(1, (int) $slot['guestLimit']);
        $this->assertArrayHasKey('duration', $slot);
        $this->assertSame(30, (int) $slot['duration']);
        $this->assertArrayHasKey('breakTime', $slot);
        $this->assertSame(0, (int) $slot['breakTime']);
        $this->assertArrayHasKey('preventSchedulingBefore', $slot);
        $this->assertArrayHasKey('sameSlotAllDays', $slot);
        $this->assertArrayHasKey('slots', $slot);
        $this->assertNotNull($slot['slots']);
    }

    // ─── Event ──────────────────────────────────────────────────────────

    /**
     * Test querying event booking product with full ticket details,
     * including translations, formatted prices, and special prices.
     */
    public function test_get_event_booking_product(): void
    {
        $bookingData = $this->createBookingProductFixture('event');

        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                sku
                urlKey
                price
                bookingProducts {
                  edges {
                    node {
                      _id
                      type
                      availableFrom
                      availableTo
                      location
                      eventTickets {
                        edges {
                          node {
                            id
                            _id
                            bookingProductId
                            price
                            qty
                            specialPrice
                            specialPriceFrom
                            specialPriceTo
                            formattedPrice
                            formattedSpecialPrice
                            translation {
                              locale
                              name
                              description
                            }
                            translations {
                              edges {
                                node {
                                  locale
                                  name
                                  description
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'id' => (string) $bookingData['product']->id,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.product');
        $this->assertProductFields($data);

        $node = $this->extractBookingNode($data, 'event');

        // Availability fields
        $this->assertNotNull($node['availableFrom']);
        $this->assertNotNull($node['availableTo']);

        // Event tickets assertions
        $this->assertArrayHasKey('eventTickets', $node);
        $ticketEdges = $node['eventTickets']['edges'] ?? [];
        $this->assertNotEmpty($ticketEdges, 'eventTickets.edges should not be empty');

        $ticket = $ticketEdges[0]['node'] ?? null;
        $this->assertNotNull($ticket, 'event ticket node is null');
        $this->assertArrayHasKey('id', $ticket);
        $this->assertArrayHasKey('_id', $ticket);
        $this->assertArrayHasKey('bookingProductId', $ticket);
        $this->assertSame((int) $bookingData['booking']->id, (int) $ticket['bookingProductId']);
        $this->assertArrayHasKey('price', $ticket);
        $this->assertSame(10.0, (float) $ticket['price']);
        $this->assertArrayHasKey('qty', $ticket);
        $this->assertSame(100, (int) $ticket['qty']);
        $this->assertArrayHasKey('specialPrice', $ticket);
        $this->assertArrayHasKey('specialPriceFrom', $ticket);
        $this->assertArrayHasKey('specialPriceTo', $ticket);

        // Formatted price assertions
        $this->assertArrayHasKey('formattedPrice', $ticket);
        $this->assertNotNull($ticket['formattedPrice'], 'formattedPrice should not be null');
        $this->assertStringContainsString('10', $ticket['formattedPrice']);

        $this->assertArrayHasKey('formattedSpecialPrice', $ticket);

        // Translation (singular - current locale)
        $this->assertArrayHasKey('translation', $ticket);
        $translation = $ticket['translation'];
        $this->assertNotNull($translation, 'translation should not be null');
        $this->assertSame('en', $translation['locale']);
        $this->assertSame('Test Ticket', $translation['name']);
        $this->assertSame('Test Ticket Description', $translation['description']);

        // Translations (plural - all locales)
        $this->assertArrayHasKey('translations', $ticket);
        $translationEdges = $ticket['translations']['edges'] ?? [];
        $this->assertNotEmpty($translationEdges, 'translations.edges should not be empty');

        $firstTranslation = $translationEdges[0]['node'] ?? null;
        $this->assertNotNull($firstTranslation);
        $this->assertSame('en', $firstTranslation['locale']);
        $this->assertSame('Test Ticket', $firstTranslation['name']);
        $this->assertSame('Test Ticket Description', $firstTranslation['description']);
    }

    /**
     * Test event booking product with multiple tickets and translations.
     */
    public function test_get_event_booking_product_multiple_tickets(): void
    {
        $bookingData = $this->createBookingProductFixture('event');

        // Add a second ticket
        $ticket2 = BookingProductEventTicket::query()->create([
            'booking_product_id' => $bookingData['booking']->id,
            'price'              => 50,
            'qty'                => 25,
            'special_price'      => 40,
            'special_price_from' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
            'special_price_to'   => Carbon::now()->addMonth()->format('Y-m-d H:i:s'),
        ]);

        DB::table('booking_product_event_ticket_translations')->insert([
            'booking_product_event_ticket_id' => $ticket2->id,
            'locale'                          => 'en',
            'name'                            => 'VIP Ticket',
            'description'                     => 'VIP Access with backstage pass',
        ]);

        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                bookingProducts {
                  edges {
                    node {
                      _id
                      type
                      eventTickets {
                        edges {
                          node {
                            id
                            price
                            qty
                            specialPrice
                            formattedPrice
                            formattedSpecialPrice
                            translation {
                              name
                              description
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'id' => (string) $bookingData['product']->id,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $node = $this->extractBookingNode($response->json('data.product'), 'event');
        $ticketEdges = $node['eventTickets']['edges'] ?? [];

        $this->assertCount(2, $ticketEdges, 'Should have exactly 2 event tickets');

        // Verify both tickets have formatted prices and translations
        foreach ($ticketEdges as $edge) {
            $t = $edge['node'];
            $this->assertNotNull($t['formattedPrice'], 'Each ticket should have formattedPrice');
            $this->assertNotNull($t['translation'], 'Each ticket should have a translation');
            $this->assertNotEmpty($t['translation']['name'], 'Each ticket translation should have a name');
        }

        // Verify second ticket values
        $vipTicket = collect($ticketEdges)->firstWhere('node.translation.name', 'VIP Ticket')['node'] ?? null;
        $this->assertNotNull($vipTicket, 'VIP ticket should exist');
        $this->assertSame(50.0, (float) $vipTicket['price']);
        $this->assertSame(25, (int) $vipTicket['qty']);
        $this->assertSame(40.0, (float) $vipTicket['specialPrice']);
    }

    /**
     * Helper method to create booking product fixtures
     */
    private function createBookingProductFixture(string $bookingType): array
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'TEST-BOOKING-QUERY-'.$bookingType.'-'.uniqid(),
        ]);

        $this->ensureInventory($product, 100);

        $booking = BookingProduct::query()->create([
            'product_id'           => $product->id,
            'type'                 => $bookingType,
            'qty'                  => 100,
            'available_every_week' => 1,
            'available_from'       => $bookingType === 'event' ? Carbon::now()->addDay()->format('Y-m-d H:i:s') : null,
            'available_to'         => $bookingType === 'event' ? Carbon::now()->addMonth()->format('Y-m-d H:i:s') : null,
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');
        $weekday = (int) Carbon::parse($tomorrow)->format('w');

        if ($bookingType === 'default') {
            BookingProductDefaultSlot::query()->create([
                'booking_product_id' => $booking->id,
                'booking_type'       => 'many',
                'duration'           => 30,
                'break_time'         => 0,
                'slots'              => [
                    (string) $weekday => [
                        ['from' => '09:00', 'to' => '10:00', 'qty' => 10, 'status' => 1],
                    ],
                ],
            ]);
        } elseif ($bookingType === 'appointment') {
            BookingProductAppointmentSlot::query()->create([
                'booking_product_id' => $booking->id,
                'duration'           => 30,
                'break_time'         => 0,
                'same_slot_all_days' => 1,
                'slots'              => [
                    ['from' => '09:00', 'to' => '10:00', 'qty' => 10, 'status' => 1],
                ],
            ]);
        } elseif ($bookingType === 'table') {
            BookingProductTableSlot::query()->create([
                'booking_product_id'        => $booking->id,
                'price_type'                => 'table',
                'guest_limit'               => 1,
                'duration'                  => 30,
                'break_time'                => 0,
                'prevent_scheduling_before' => 0,
                'same_slot_all_days'        => 1,
                'slots'                     => [
                    ['from' => '09:00', 'to' => '10:00', 'qty' => 10, 'status' => 1],
                ],
            ]);
        } elseif ($bookingType === 'rental') {
            BookingProductRentalSlot::query()->create([
                'booking_product_id' => $booking->id,
                'renting_type'       => 'daily',
                'daily_price'        => 10,
                'hourly_price'       => 0,
                'same_slot_all_days' => 1,
                'slots'              => [],
            ]);
        } elseif ($bookingType === 'event') {
            /** @var BookingProductEventTicket $ticket */
            $ticket = BookingProductEventTicket::query()->create([
                'booking_product_id'   => $booking->id,
                'price'                => 10,
                'qty'                  => 100,
                'special_price_from'   => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                'special_price_to'     => Carbon::now()->addMonth()->format('Y-m-d H:i:s'),
            ]);

            DB::table('booking_product_event_ticket_translations')->insert([
                'booking_product_event_ticket_id' => $ticket->id,
                'locale'                          => 'en',
                'name'                            => 'Test Ticket',
                'description'                     => 'Test Ticket Description',
            ]);
        }

        return [
            'product'      => $product,
            'booking'      => $booking,
            'tomorrowDate' => $tomorrow,
        ];
    }
}
