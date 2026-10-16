<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Notification\Models\Notification;
use Webkul\Sales\Models\Order;

class NotificationTest extends AdminApiTestCase
{
    private function createNotification(bool $read = false): Notification
    {
        $this->seedRequiredData();

        $order = Order::factory()->create([
            'increment_id' => 'N-'.uniqid(),
            'channel_id' => Channel::first()->id,
            'status' => 'pending',
        ]);

        return Notification::create(['type' => 'order', 'order_id' => $order->id, 'read' => $read]);
    }

    public function test_list_returns_notifications_with_counts(): void
    {
        $notification = $this->createNotification();

        $response = $this->adminGet($this->createAdmin(), '/api/admin/notifications?per_page=50');

        $response->assertOk();
        $this->assertContains($notification->id, array_column($response->json('data'), 'id'));
        $this->assertGreaterThanOrEqual(1, $response->json('meta.totalUnread'));
        $this->assertIsArray($response->json('meta.statusCounts'));
    }

    public function test_list_filters_by_read_state(): void
    {
        $this->createNotification(true);

        $response = $this->adminGet($this->createAdmin(), '/api/admin/notifications?read=0&per_page=50');

        $response->assertOk();

        foreach ($response->json('data') as $row) {
            $this->assertFalse($row['read']);
        }
    }

    public function test_read_marks_one_notification(): void
    {
        $notification = $this->createNotification();

        $response = $this->adminPost($this->createAdmin(), '/api/admin/notifications/'.$notification->order_id.'/read');

        $response->assertOk();
        $this->assertTrue($response->json('read'));
        $this->assertSame(1, (int) $notification->fresh()->read);
    }

    public function test_read_unknown_order_returns_404(): void
    {
        $response = $this->adminPost($this->createAdmin(), '/api/admin/notifications/999999999/read');

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_read_all_marks_everything(): void
    {
        $notification = $this->createNotification();

        $response = $this->adminPost($this->createAdmin(), '/api/admin/notifications/read-all');

        $response->assertOk();
        $this->assertSame(0, $response->json('totalUnread'));
        $this->assertSame(1, (int) $notification->fresh()->read);
    }

    public function test_requires_authentication(): void
    {
        $this->assertSame(401, $this->publicGet('/api/admin/notifications')->getStatusCode());
        $this->assertSame(401, $this->publicPost('/api/admin/notifications/read-all')->getStatusCode());
    }
}
