<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\User\Models\Role;

/**
 * REST coverage for GET /api/admin/bookings/calendar.
 */
class SalesBookingsCalendarTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function seedBooking(string $from, string $to): int
    {
        $order = $this->bootstrapAdminOrder('processing', false);
        $item = $order->items->first();

        DB::table('order_items')->where('id', $item->id)->update([
            'additional' => json_encode([
                'attributes' => [
                    ['attribute_name' => 'Booking From', 'option_label' => $from],
                    ['attribute_name' => 'Booking Till', 'option_label' => $to],
                ],
            ]),
        ]);

        return DB::table('bookings')->insertGetId([
            'product_id' => $item->product_id,
            'order_item_id' => $item->id,
            'order_id' => $order->id,
            'qty' => 1,
            'from' => Carbon::parse($from)->timestamp,
            'to' => Carbon::parse($to)->timestamp,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->publicGet('/api/admin/bookings/calendar')->assertStatus(401);
    }

    public function test_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $this->adminGet($admin, '/api/admin/bookings/calendar')->assertStatus(403);
    }

    public function test_custom_role_with_bookings_key_can_read(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => ['sales', 'sales.bookings'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $this->adminGet($admin, '/api/admin/bookings/calendar')->assertOk();
    }

    public function test_defaults_to_the_current_week(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/bookings/calendar');

        $response->assertOk();
        expect($response->json('0.start'))->toBe(Carbon::now()->startOfWeek()->format('Y-m-d'));
        expect($response->json('0.end'))->toBe(Carbon::now()->endOfWeek()->format('Y-m-d'));
        expect($response->json('0.bookings'))->toBeArray();
    }

    public function test_returns_bookings_overlapping_the_window(): void
    {
        $admin = $this->createAdmin();
        $inside = $this->seedBooking('2031-03-12 10:00:00', '2031-03-12 11:00:00');
        $outside = $this->seedBooking('2031-04-20 10:00:00', '2031-04-20 11:00:00');

        $response = $this->adminGet($admin, '/api/admin/bookings/calendar?start=2031-03-10&end=2031-03-16');

        $response->assertOk();
        expect($response->json('0.start'))->toBe('2031-03-10');
        expect($response->json('0.end'))->toBe('2031-03-16');

        $bookings = collect($response->json('0.bookings'));
        expect($bookings->pluck('id')->all())->toContain($inside);
        expect($bookings->pluck('id')->all())->not->toContain($outside);

        $row = $bookings->firstWhere('id', $inside);
        expect($row)->toHaveKeys([
            'id', 'orderId', 'start', 'end', 'status', 'email', 'fullName', 'createdAt',
            'productSku', 'productName', 'address', 'contact', 'city', 'state', 'country', 'postcode', 'attributes',
        ]);
        expect($row['start'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2} (AM|PM)$/');
        expect($row['attributes'])->toHaveCount(2);
        expect($row['attributes'][0]['attribute_name'])->toBe('Booking From');
    }

    public function test_invalid_date_returns_422(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/bookings/calendar?start=12-03-2031')->assertStatus(422);
    }

    public function test_detail_route_still_404s_for_unknown_id(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/bookings/99999999')->assertStatus(404);
    }
}
