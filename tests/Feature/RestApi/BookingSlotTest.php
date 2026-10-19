<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\BookingProduct\Models\BookingProductAppointmentSlot;
use Webkul\BookingProduct\Models\BookingProductDefaultSlot;
use Webkul\BookingProduct\Models\BookingProductEventTicket;
use Webkul\BookingProduct\Models\BookingProductRentalSlot;
use Webkul\BookingProduct\Models\BookingProductTableSlot;

class BookingSlotTest extends RestApiTestCase
{
    /**
     * The endpoint.
     */
    private string $endpoint = '/api/shop/booking-slots';

    // ── Flat types (default / appointment / table / event) ─────────────

    /**
     * Test booking slots default.
     */
    public function test_booking_slots_default(): void
    {
        $this->seedRequiredData();
        $f = $this->createBookingFixture('default');

        $response = $this->publicGet($this->url((int) $f['booking']->id, $f['tomorrowDate']));

        $response->assertOk();
        $slots = $response->json();
        expect($slots)->toBeArray()->not()->toBeEmpty();
        foreach ($slots as $slot) {
            $this->assertFlatSlotStructure($slot);
        }
    }

    /**
     * Test booking slots appointment.
     */
    public function test_booking_slots_appointment(): void
    {
        $this->seedRequiredData();
        $f = $this->createBookingFixture('appointment');

        $response = $this->publicGet($this->url((int) $f['booking']->id, $f['tomorrowDate']));

        $response->assertOk();
        $slots = $response->json();
        expect($slots)->toBeArray()->not()->toBeEmpty();
        foreach ($slots as $slot) {
            $this->assertFlatSlotStructure($slot);
        }
    }

    /**
     * Test booking slots table.
     */
    public function test_booking_slots_table(): void
    {
        $this->seedRequiredData();
        $f = $this->createBookingFixture('table');

        $response = $this->publicGet($this->url((int) $f['booking']->id, $f['tomorrowDate']));

        $response->assertOk();
        $slots = $response->json();
        expect($slots)->toBeArray()->not()->toBeEmpty();
        foreach ($slots as $slot) {
            $this->assertFlatSlotStructure($slot);
        }
    }

    /**
     * Test booking slots event.
     */
    public function test_booking_slots_event(): void
    {
        $this->seedRequiredData();
        $f = $this->createBookingFixture('event');

        $response = $this->publicGet($this->url((int) $f['booking']->id, $f['tomorrowDate']));

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Rental (nested) ────────────────────────────────────────────────

    /**
     * Test booking slots rental.
     */
    public function test_booking_slots_rental(): void
    {
        $this->seedRequiredData();
        $f = $this->createBookingFixture('rental');

        $response = $this->publicGet($this->url((int) $f['booking']->id, $f['tomorrowDate']));

        $response->assertOk();
        $groups = $response->json();
        expect($groups)->toBeArray()->not()->toBeEmpty();
        foreach ($groups as $group) {
            $this->assertRentalGroupStructure($group);
        }
    }

    // ── Validation ─────────────────────────────────────────────────────

    /**
     * Test missing id returns 400.
     */
    public function test_missing_id_returns_400(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->endpoint.'?date=2026-03-26');

        expect($response->getStatusCode())->toBe(400);
    }

    /**
     * Test missing date returns 400.
     */
    public function test_missing_date_returns_400(): void
    {
        $this->seedRequiredData();
        $f = $this->createBookingFixture('default');

        $response = $this->publicGet($this->endpoint.'?id='.$f['booking']->id);

        expect($response->getStatusCode())->toBe(400);
    }

    /**
     * Test invalid product id returns 400.
     */
    public function test_invalid_product_id_returns_400(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url(99999999, '2026-03-26'));

        expect($response->getStatusCode())->toBe(400);
    }

    /**
     * Url.
     */
    private function url(int $id, string $date): string
    {
        return $this->endpoint.'?id='.$id.'&date='.$date;
    }

    /**
     * Create booking fixture.
     */
    private function createBookingFixture(string $type): array
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'TEST-RBSLOT-'.$type.'-'.uniqid(),
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
                        [
                            'from' => '09:00',
                            'to' => '17:00',
                            'qty' => 10,
                            'status' => 1,
                        ],
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
                    [
                        'from' => '09:00',
                        'to' => '17:00',
                        'qty' => 10,
                        'status' => 1,
                    ],
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
                    [
                        'from' => '09:00',
                        'to' => '17:00',
                        'qty' => 10,
                        'status' => 1,
                    ],
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
                    [
                        'from' => '09:00',
                        'to' => '17:00',
                    ],
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
     * Assert flat slot structure.
     */
    private function assertFlatSlotStructure(array $slot): void
    {
        expect($slot)->toHaveKeys(['slotId', 'from', 'to', 'timestamp', 'qty']);
        expect($slot['from'])->not()->toBeNull();
        expect($slot['to'])->not()->toBeNull();
        expect($slot['timestamp'])->not()->toBeNull();
        expect($slot['timestamp'])->toMatch('/^\d+-\d+$/');
    }

    /**
     * Assert rental group structure.
     */
    private function assertRentalGroupStructure(array $group): void
    {
        expect($group)->toHaveKeys(['slotId', 'time', 'slots']);
        expect($group['time'])->not()->toBeNull();
        expect($group['slots'])->toBeArray()->not()->toBeEmpty();

        foreach ($group['slots'] as $sub) {
            expect($sub)->toHaveKeys(['from', 'to', 'timestamp', 'qty']);
            expect($sub['from'])->not()->toBeNull();
            expect($sub['to'])->not()->toBeNull();
            expect($sub['timestamp'])->toMatch('/^\d+-\d+$/');
        }
    }
}
