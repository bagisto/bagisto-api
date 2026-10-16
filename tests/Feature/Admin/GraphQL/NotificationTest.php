<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Notification\Models\Notification;
use Webkul\Sales\Models\Order;

class NotificationTest extends AdminApiTestCase
{
    private function createNotification(): Notification
    {
        $this->seedRequiredData();

        $order = Order::factory()->create([
            'increment_id' => 'N-'.uniqid(),
            'channel_id' => Channel::first()->id,
            'status' => 'pending',
        ]);

        return Notification::create(['type' => 'order', 'order_id' => $order->id, 'read' => false]);
    }

    public function test_list_and_summary(): void
    {
        $notification = $this->createNotification();
        $admin = $this->createAdmin();

        $list = $this->adminGraphQL(<<<'GQL'
            {
              adminNotifications(first: 50, read: 0) {
                edges {
                  node {
                    _id
                    orderId
                    read
                    orderStatus
                    orderStatusLabel
                    createdAt
                  }
                }
              }
            }
        GQL, [], $admin);

        $list->assertOk();
        $this->assertNull($list->json('errors'));
        $this->assertContains($notification->id, array_column(array_column($list->json('data.adminNotifications.edges'), 'node'), '_id'));

        $summary = $this->adminGraphQL('{ summaryAdminNotification { totalUnread statusCounts } }', [], $admin);

        $this->assertNull($summary->json('errors'));
        $this->assertGreaterThanOrEqual(1, $summary->json('data.summaryAdminNotification.totalUnread'));
    }

    public function test_read_and_read_all(): void
    {
        $notification = $this->createNotification();
        $admin = $this->createAdmin();

        $read = $this->adminGraphQL(<<<'GQL'
            mutation ($input: readAdminNotificationInput!) {
              readAdminNotification(input: $input) {
                adminNotification {
                  _id
                  read
                }
              }
            }
        GQL, ['input' => ['id' => '/api/admin/notifications', 'orderId' => $notification->order_id]], $admin);

        $this->assertNull($read->json('errors'));
        $this->assertTrue($read->json('data.readAdminNotification.adminNotification.read'));

        $this->createNotification();

        $readAll = $this->adminGraphQL(<<<'GQL'
            mutation ($input: readAllAdminNotificationInput!) {
              readAllAdminNotification(input: $input) {
                adminNotification {
                  totalUnread
                  message
                }
              }
            }
        GQL, ['input' => ['id' => '/api/admin/notifications']], $admin);

        $this->assertNull($readAll->json('errors'));
        $this->assertSame(0, $readAll->json('data.readAllAdminNotification.adminNotification.totalUnread'));
    }

    public function test_read_unknown_order_errors(): void
    {
        $response = $this->adminGraphQL(<<<'GQL'
            mutation ($input: readAdminNotificationInput!) {
              readAdminNotification(input: $input) {
                adminNotification {
                  _id
                }
              }
            }
        GQL, ['input' => ['id' => '/api/admin/notifications', 'orderId' => 999999999]], $this->createAdmin());

        $this->assertNotEmpty($response->json('errors'));
    }

    public function test_list_requires_authentication(): void
    {
        $this->assertNotEmpty($this->adminGraphQL('{ adminNotifications { edges { node { _id } } } }')->json('errors'));
    }
}
