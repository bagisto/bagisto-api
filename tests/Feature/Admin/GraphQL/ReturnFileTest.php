<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\RMA\Models\RMA;
use Webkul\RMA\Models\RMAImage;
use Webkul\RMA\Models\RMAItem;
use Webkul\RMA\Models\RMAMessage;
use Webkul\RMA\Models\RMAStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;

class ReturnFileTest extends AdminApiTestCase
{
    /**
     * Test the return detail points each image url at the REST image endpoint.
     */
    public function test_return_image_urls_point_at_the_image_endpoint(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();
        $image = $this->createImage($data['rma']);

        $query = <<<GQL
            query {
              adminReturn(id: "/api/admin/rma/requests/{$data['rma']->id}") {
                _id
                images
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $row = collect($response->json('data.adminReturn.images'))->firstWhere('id', $image->id);
        expect($row)->not->toBeNull();
        expect($row['url'])->toEndWith('/api/admin/rma/requests/images/'.$image->id);
    }

    /**
     * Test a message attachment url points at the REST attachment endpoint.
     */
    public function test_message_attachment_url_points_at_the_attachment_endpoint(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $message = RMAMessage::query()->create([
            'rma_id' => $data['rma']->id,
            'message' => 'Attached.',
            'is_admin' => 1,
            'attachment_path' => 'rmas/'.$data['rma']->id.'/conversations/0/'.Str::random(40).'.png',
            'attachment' => 'attached.png',
        ]);

        $response = $this->adminGraphQL(
            "query { adminReturnMessages(returnId: {$data['rma']->id}) { _id attachment attachmentUrl } }",
            [],
            $admin
        );

        $response->assertOk();
        $node = collect($response->json('data.adminReturnMessages'))->firstWhere('_id', $message->id);
        expect($node)->not->toBeNull();
        expect($node['attachment'])->toBe('attached.png');
        expect($node['attachmentUrl'])->toEndWith('/api/admin/rma/messages/'.$message->id.'/attachment');
    }

    /**
     * Test a message without an attachment has no attachment url.
     */
    public function test_message_without_attachment_has_no_url(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $message = RMAMessage::query()->create([
            'rma_id' => $data['rma']->id,
            'message' => 'No file.',
            'is_admin' => 1,
        ]);

        $response = $this->adminGraphQL(
            "query { adminReturnMessages(returnId: {$data['rma']->id}) { _id attachmentUrl } }",
            [],
            $admin
        );

        $response->assertOk();
        $node = collect($response->json('data.adminReturnMessages'))->firstWhere('_id', $message->id);
        expect($node)->not->toBeNull();
        expect($node['attachmentUrl'])->toBeNull();
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
    }

    /**
     * Store a PNG on the private disk as a return image.
     */
    protected function createImage(RMA $rma): RMAImage
    {
        $path = 'rmas/'.$rma->id.'/images/'.Str::random(40).'.png';

        Storage::disk('private')->put($path, 'image-bytes');

        return RMAImage::query()->create([
            'rma_id' => $rma->id,
            'path' => $path,
        ]);
    }

    /**
     * Create a return request on a completed order.
     */
    protected function createReturn(): array
    {
        $channel = Channel::first();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => 'Jane',
            'customer_last_name' => 'Doe',
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'ADMIN-RMA-FILE-1',
            'type' => 'simple',
            'name' => 'Returnable Product',
        ]);

        $status = RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'color' => '#f00',
            'default' => 1,
        ]);

        $rma = RMA::create([
            'order_id' => $order->id,
            'rma_status_id' => $status->id,
            'information' => 'Damaged on arrival',
            'package_condition' => 'opened',
        ]);

        RMAItem::create([
            'rma_id' => $rma->id,
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'resolution' => 'return',
        ]);

        return compact('order', 'orderItem', 'rma', 'status');
    }
}
