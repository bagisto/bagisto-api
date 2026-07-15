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

class BookingSlotQueryTest extends GraphQLTestCase
{
    /**
     * Flat query for non-rental types (default, appointment, table, event).
     */
    private string $flatQuery = <<<'GQL'
        query {
          bookingSlots(id: %d, date: "%s") {
            slotId
            from
            to
            timestamp
            qty
          }
        }
    GQL;

    /**
     * Grouped query for rental hourly type — returns groups with nested slots.
     */
    private string $rentalQuery = <<<'GQL'
        query {
          bookingSlots(id: %d, date: "%s") {
            slotId
            time
            slots
          }
        }
    GQL;

    /**
     * Create a booking product fixture with slots for the given type.
     */
    private function createBookingFixture(string $type): array
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'TEST-BSLOT-'.$type.'-'.uniqid(),
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');
        $weekday = (int) Carbon::parse($tomorrow)->format('w');

        $booking = BookingProduct::query()->create([
            'product_id' => $product->id,
            'type' => $type,
            'qty' => 100,
            'available_every_week' => 1,
            'available_from' => $type === 'event' ? Carbon::now()->addDay()->format('Y-m-d H:i:s') : null,
            'available_to' => $type === 'event' ? Carbon::now()->addMonth()->format('Y-m-d H:i:s') : null,
        ]);

        if ($type === 'default') {
            BookingProductDefaultSlot::query()->create([
                'booking_product_id' => $booking->id,
                'booking_type' => 'many',
                'duration' => 30,
                'break_time' => 0,
                'slots' => [
                    (string) $weekday => [
                        ['from' => '09:00', 'to' => '17:00', 'qty' => 10, 'status' => 1],
                    ],
                ],
            ]);
        } elseif ($type === 'appointment') {
            BookingProductAppointmentSlot::query()->create([
                'booking_product_id' => $booking->id,
                'duration' => 45,
                'break_time' => 0,
                'same_slot_all_days' => 1,
                'slots' => [
                    ['from' => '09:00', 'to' => '17:00', 'qty' => 10, 'status' => 1],
                ],
            ]);
        } elseif ($type === 'table') {
            BookingProductTableSlot::query()->create([
                'booking_product_id' => $booking->id,
                'price_type' => 'table',
                'guest_limit' => 4,
                'duration' => 45,
                'break_time' => 0,
                'prevent_scheduling_before' => 0,
                'same_slot_all_days' => 1,
                'slots' => [
                    ['from' => '09:00', 'to' => '17:00', 'qty' => 10, 'status' => 1],
                ],
            ]);
        } elseif ($type === 'rental') {
            BookingProductRentalSlot::query()->create([
                'booking_product_id' => $booking->id,
                'renting_type' => 'hourly',
                'daily_price' => 0,
                'hourly_price' => 5,
                'same_slot_all_days' => 1,
                'slots' => [
                    ['from' => '09:00', 'to' => '17:00'],
                ],
            ]);
        } elseif ($type === 'event') {
            $ticket = BookingProductEventTicket::query()->create([
                'booking_product_id' => $booking->id,
                'price' => 10,
                'qty' => 100,
                'special_price_from' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                'special_price_to' => Carbon::now()->addMonth()->format('Y-m-d H:i:s'),
            ]);

            DB::table('booking_product_event_ticket_translations')->insert([
                'booking_product_event_ticket_id' => $ticket->id,
                'locale' => 'en',
                'name' => 'Test Event Ticket',
                'description' => 'Test event ticket description',
            ]);
        }

        return [
            'product' => $product,
            'booking' => $booking,
            'tomorrowDate' => $tomorrow,
        ];
    }

    /**
     * Create a rental booking fixture with multiple admin-configured slot groups (like Rental Product 2).
     */
    private function createMultiGroupRentalFixture(): array
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'TEST-BSLOT-rental-multi-'.uniqid(),
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');

        $booking = BookingProduct::query()->create([
            'product_id' => $product->id,
            'type' => 'rental',
            'qty' => 100,
            'available_every_week' => 1,
        ]);

        // Two slot groups like in the screenshot: 10:00-12:00 and 12:00-21:00
        BookingProductRentalSlot::query()->create([
            'booking_product_id' => $booking->id,
            'renting_type' => 'hourly',
            'daily_price' => 0,
            'hourly_price' => 5,
            'same_slot_all_days' => 1,
            'slots' => [
                ['from' => '10:00', 'to' => '12:00'],
                ['from' => '12:00', 'to' => '21:00'],
            ],
        ]);

        return [
            'product' => $product,
            'booking' => $booking,
            'tomorrowDate' => $tomorrow,
        ];
    }

    private function buildFlatQuery(int $bookingProductId, string $date): string
    {
        return sprintf($this->flatQuery, $bookingProductId, $date);
    }

    private function buildRentalQuery(int $bookingProductId, string $date): string
    {
        return sprintf($this->rentalQuery, $bookingProductId, $date);
    }

    /**
     * Assert flat slot structure (non-rental types).
     */
    private function assertFlatSlotStructure(array $slot): void
    {
        $this->assertArrayHasKey('slotId', $slot);
        $this->assertArrayHasKey('from', $slot);
        $this->assertArrayHasKey('to', $slot);
        $this->assertArrayHasKey('timestamp', $slot);
        $this->assertArrayHasKey('qty', $slot);
        $this->assertNotNull($slot['from']);
        $this->assertNotNull($slot['to']);
    }

    /**
     * Assert rental group structure — each group has time + nested slots array.
     */
    private function assertRentalGroupStructure(array $group): void
    {
        $this->assertArrayHasKey('slotId', $group);
        $this->assertArrayHasKey('time', $group);
        $this->assertArrayHasKey('slots', $group);
        $this->assertNotNull($group['time'], 'Rental group must have a time label');
        $this->assertIsArray($group['slots']);
        $this->assertNotEmpty($group['slots'], 'Rental group must have at least one sub-slot');

        foreach ($group['slots'] as $subSlot) {
            $this->assertArrayHasKey('from', $subSlot);
            $this->assertArrayHasKey('to', $subSlot);
            $this->assertArrayHasKey('timestamp', $subSlot);
            $this->assertArrayHasKey('qty', $subSlot);
            $this->assertNotNull($subSlot['from']);
            $this->assertNotNull($subSlot['to']);
            $this->assertNotNull($subSlot['timestamp']);
            $this->assertMatchesRegularExpression('/^\d+-\d+$/', $subSlot['timestamp']);
        }
    }

    // ─── Default Booking (flat) ─────────────────────────────────────────

    public function test_booking_slots_for_default_type(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createBookingFixture('default');

        $response = $this->graphQL($this->buildFlatQuery((int) $fixture['booking']->id, $fixture['tomorrowDate']));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $slots = $response->json('data.bookingSlots');
        $this->assertNotNull($slots);
        $this->assertIsArray($slots);
        $this->assertNotEmpty($slots, 'Default booking should return at least one slot');

        foreach ($slots as $slot) {
            $this->assertFlatSlotStructure($slot);
            $this->assertNotNull($slot['timestamp']);
            $this->assertMatchesRegularExpression('/^\d+-\d+$/', $slot['timestamp']);
        }
    }

    // ─── Appointment Booking (flat) ─────────────────────────────────────

    public function test_booking_slots_for_appointment_type(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createBookingFixture('appointment');

        $response = $this->graphQL($this->buildFlatQuery((int) $fixture['booking']->id, $fixture['tomorrowDate']));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $slots = $response->json('data.bookingSlots');
        $this->assertNotNull($slots);
        $this->assertIsArray($slots);
        $this->assertNotEmpty($slots, 'Appointment booking should return at least one slot');

        foreach ($slots as $slot) {
            $this->assertFlatSlotStructure($slot);
            $this->assertNotNull($slot['timestamp']);
            $this->assertMatchesRegularExpression('/^\d+-\d+$/', $slot['timestamp']);
        }
    }

    // ─── Table Booking (flat) ───────────────────────────────────────────

    public function test_booking_slots_for_table_type(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createBookingFixture('table');

        $response = $this->graphQL($this->buildFlatQuery((int) $fixture['booking']->id, $fixture['tomorrowDate']));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $slots = $response->json('data.bookingSlots');
        $this->assertNotNull($slots);
        $this->assertIsArray($slots);
        $this->assertNotEmpty($slots, 'Table booking should return at least one slot');

        foreach ($slots as $slot) {
            $this->assertFlatSlotStructure($slot);
            $this->assertNotNull($slot['timestamp']);
            $this->assertMatchesRegularExpression('/^\d+-\d+$/', $slot['timestamp']);
        }
    }

    // ─── Rental Booking (grouped) — single group ────────────────────────

    public function test_booking_slots_for_rental_hourly_type(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createBookingFixture('rental');

        $response = $this->graphQL($this->buildRentalQuery((int) $fixture['booking']->id, $fixture['tomorrowDate']));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $groups = $response->json('data.bookingSlots');
        $this->assertNotNull($groups);
        $this->assertIsArray($groups);
        $this->assertNotEmpty($groups, 'Rental hourly booking should return at least one group');

        // Single group (09:00-17:00) with 8 hourly sub-slots
        $this->assertCount(1, $groups);

        $group = $groups[0];
        $this->assertRentalGroupStructure($group);
        $this->assertGreaterThan(1, count($group['slots']), 'Group should have multiple hourly sub-slots');
    }

    // ─── Rental Booking (grouped) — multiple groups ─────────────────────

    public function test_booking_slots_for_rental_hourly_with_multiple_groups(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createMultiGroupRentalFixture();

        $response = $this->graphQL($this->buildRentalQuery((int) $fixture['booking']->id, $fixture['tomorrowDate']));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $groups = $response->json('data.bookingSlots');
        $this->assertNotNull($groups);
        $this->assertIsArray($groups);

        // Should have 2 groups: 10:00-12:00 and 12:00-21:00
        $this->assertCount(2, $groups, 'Should have 2 time range groups');

        foreach ($groups as $group) {
            $this->assertRentalGroupStructure($group);
        }

        // Collect group details
        $groupDetails = [];
        foreach ($groups as $group) {
            $groupDetails[] = [
                'time' => $group['time'],
                'slotCount' => count($group['slots']),
            ];
        }

        // Sort by slot count to assert predictably
        usort($groupDetails, fn ($a, $b) => $a['slotCount'] <=> $b['slotCount']);

        // Smaller group (10:00-12:00) = 2 hourly slots
        $this->assertSame(2, $groupDetails[0]['slotCount'], 'Smaller group should have 2 hourly slots');
        // Larger group (12:00-21:00) = 9 hourly slots
        $this->assertSame(9, $groupDetails[1]['slotCount'], 'Larger group should have 9 hourly slots');

        // Verify first sub-slot of each group has correct from/to
        foreach ($groups as $group) {
            $firstSlot = $group['slots'][0];
            $lastSlot = end($group['slots']);

            // First slot's "from" should match the start of the group time
            $this->assertNotNull($firstSlot['from']);
            $this->assertNotNull($lastSlot['to']);
        }
    }

    // ─── Event Booking (flat) ───────────────────────────────────────────

    public function test_booking_slots_for_event_type(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createBookingFixture('event');

        $response = $this->graphQL($this->buildFlatQuery((int) $fixture['booking']->id, $fixture['tomorrowDate']));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $slots = $response->json('data.bookingSlots');
        $this->assertNotNull($slots);
        $this->assertIsArray($slots);
    }

    // ─── Edge cases ─────────────────────────────────────────────────────

    public function test_booking_slots_with_past_date_returns_empty(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createBookingFixture('appointment');

        $pastDate = Carbon::now()->subDays(7)->format('Y-m-d');

        $response = $this->graphQL($this->buildFlatQuery((int) $fixture['booking']->id, $pastDate));

        $response->assertSuccessful();

        $json = $response->json();
        if (! isset($json['errors'])) {
            $slots = $response->json('data.bookingSlots');
            $this->assertIsArray($slots);
            $this->assertEmpty($slots, 'Past date should return no available slots');
        }
    }
}
