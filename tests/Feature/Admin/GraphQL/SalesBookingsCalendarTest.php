<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\User\Models\Role;

/**
 * GraphQL coverage for the getAdminBookingCalendar query.
 */
class SalesBookingsCalendarTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected const QUERY = 'query Calendar($start: String, $end: String) { getAdminBookingCalendar(start: $start, end: $end) { start end bookings } }';

    public function test_returns_bookings_in_the_window(): void
    {
        $admin = $this->createAdmin();
        $order = $this->bootstrapAdminOrder('processing', false);
        $item = $order->items->first();

        $bookingId = DB::table('bookings')->insertGetId([
            'product_id' => $item->product_id,
            'order_item_id' => $item->id,
            'order_id' => $order->id,
            'qty' => 1,
            'from' => Carbon::parse('2031-03-12 10:00:00')->timestamp,
            'to' => Carbon::parse('2031-03-12 11:00:00')->timestamp,
        ]);

        $response = $this->adminGraphQL(self::QUERY, ['start' => '2031-03-10', 'end' => '2031-03-16'], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.getAdminBookingCalendar.start'))->toBe('2031-03-10');
        expect($response->json('data.getAdminBookingCalendar.end'))->toBe('2031-03-16');
        expect(collect($response->json('data.getAdminBookingCalendar.bookings'))->pluck('id')->all())->toContain($bookingId);
    }

    public function test_invalid_date_returns_error(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGraphQL(self::QUERY, ['start' => 'not-a-date'], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_requires_authentication(): void
    {
        $response = $this->adminGraphQL(self::QUERY, []);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_no_permission_returns_error(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->adminGraphQL(self::QUERY, [], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }
}
