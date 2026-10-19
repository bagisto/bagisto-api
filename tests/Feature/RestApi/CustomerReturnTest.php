<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\RMA\Models\RMA;
use Webkul\RMA\Models\RMACustomField;
use Webkul\RMA\Models\RMACustomFieldOption;
use Webkul\RMA\Models\RMAItem;
use Webkul\RMA\Models\RMAStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;

class CustomerReturnTest extends RestApiTestCase
{
    /**
     * Test list returns own returns.
     */
    public function test_list_returns_own_returns(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/returns');

        $response->assertOk();
        $row = $response->json('0');
        expect((int) $row['id'])->toBe($data['rma']->id);
        expect((int) $row['statusId'])->toBe(1);
        expect($row['item']['sku'])->toBe('RMA-SKU-1');
    }

    /**
     * Test view own return.
     */
    public function test_view_own_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/returns/'.$data['rma']->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($data['rma']->id);
        expect($response->json('information'))->toBe('Item arrived damaged');
        expect($response->json('item.resolution'))->toBe('return');
        expect($response->json('images'))->toBeArray();
    }

    /**
     * Test cannot view another customers return.
     */
    public function test_cannot_view_another_customers_return(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $data = $this->createReturn($owner);

        $other = $this->createCustomer();

        $response = $this->authenticatedGet($other, '/api/shop/returns/'.$data['rma']->id);

        expect($response->getStatusCode())->toBeIn([403, 404]);
    }

    /**
     * Test requires authentication.
     */
    public function test_requires_authentication(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/returns');

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    /**
     * Test create return.
     */
    public function test_create_return(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'default' => 1,
        ]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 1,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'information' => 'Damaged',
            'agreement' => true,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(1);
        expect($response->json('item.resolution'))->toBe('return');
        $this->assertDatabaseHas('rma', [
            'order_id' => $seed['order']->id,
            'rma_status_id' => 1,
        ]);
    }

    /**
     * Test create rejects qty over returnable.
     */
    public function test_create_rejects_qty_over_returnable(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'default' => 1,
        ]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 999,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'agreement' => true,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
        $this->assertDatabaseMissing('rma', ['order_id' => $seed['order']->id]);
    }

    /**
     * Test create requires agreement.
     */
    public function test_create_requires_agreement(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'default' => 1,
        ]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 1,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'agreement' => false,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    /**
     * Test returnable items.
     */
    public function test_returnable_items(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/returnable-items?order_id='.$seed['order']->id);

        $response->assertOk();
        $row = $response->json('0');
        expect((int) $row['orderItemId'])->toBe($seed['orderItem']->id);
        expect((int) $row['forReturnQuantity'])->toBe(2);
    }

    /**
     * Test return reasons.
     */
    public function test_return_reasons(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/return-reasons?resolution_type=return');

        $response->assertOk();
        expect($response->json())->toBeArray();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    /**
     * Test cancel return.
     */
    public function test_cancel_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns/'.$data['rma']->id.'/cancel', []);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(9);
        $this->assertDatabaseHas('rma', [
            'id' => $data['rma']->id,
            'rma_status_id' => 9,
        ]);
    }

    /**
     * Test close return.
     */
    public function test_close_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns/'.$data['rma']->id.'/close', []);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(6);
    }

    /**
     * Test cancel is refused once the package is received.
     */
    public function test_cancel_is_refused_once_the_package_is_received(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);
        $data['rma']->update(['rma_status_id' => 5]);

        $response = $this->authenticatedPost($customer, '/api/shop/returns/'.$data['rma']->id.'/cancel', []);

        expect($response->getStatusCode())->toBe(400);
        $this->assertDatabaseHas('rma', [
            'id' => $data['rma']->id,
            'rma_status_id' => 5,
        ]);
    }

    /**
     * Test close is refused for a solved return.
     */
    public function test_close_is_refused_for_a_solved_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);
        $data['rma']->update(['rma_status_id' => 6]);

        $response = $this->authenticatedPost($customer, '/api/shop/returns/'.$data['rma']->id.'/close', []);

        expect($response->getStatusCode())->toBe(400);
    }

    /**
     * Test close and reopen are refused once the return window has passed.
     */
    public function test_close_and_reopen_are_refused_once_the_return_window_has_passed(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        DB::table('rma')->where('id', $data['rma']->id)->update(['created_at' => now()->subDays(60)]);

        $close = $this->authenticatedPost($customer, '/api/shop/returns/'.$data['rma']->id.'/close', []);
        expect($close->getStatusCode())->toBe(400);

        $data['rma']->update(['rma_status_id' => 9]);

        $reopen = $this->authenticatedPost($customer, '/api/shop/returns/'.$data['rma']->id.'/reopen', []);
        expect($reopen->getStatusCode())->toBe(400);
    }

    /**
     * Test cancel another customers return is blocked.
     */
    public function test_cancel_another_customers_return_is_blocked(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $data = $this->createReturn($owner);
        $other = $this->createCustomer();

        $response = $this->authenticatedPost($other, '/api/shop/returns/'.$data['rma']->id.'/cancel', []);

        expect($response->getStatusCode())->toBeIn([403, 404]);
        $this->assertDatabaseMissing('rma', [
            'id' => $data['rma']->id,
            'rma_status_id' => 9,
        ]);
    }

    /**
     * Test send and list messages.
     */
    public function test_send_and_list_messages(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $send = $this->authenticatedPost($customer, '/api/shop/return-messages', [
            'return_id' => $data['rma']->id,
            'message' => 'Any update?',
        ]);

        expect($send->getStatusCode())->toBeIn([200, 201]);
        expect($send->json('message'))->toBe('Any update?');
        expect($send->json('isAdmin'))->toBeFalse();

        $list = $this->authenticatedGet($customer, '/api/shop/return-messages?return_id='.$data['rma']->id);

        $list->assertOk();
        expect(count($list->json()))->toBeGreaterThan(0);
        expect($list->json('0.message'))->toBe('Any update?');
    }

    /**
     * Test send message accepts multipart with an attachment.
     */
    public function test_send_message_accepts_multipart_with_an_attachment(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/return-messages', [
            'return_id' => $data['rma']->id,
            'message' => 'Photo of the damaged zipper attached.',
            'file' => UploadedFile::fake()->image('zipper.png'),
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('message'))->toBe('Photo of the damaged zipper attached.');
        expect($response->json('attachment'))->toBe('zipper.png');
        expect($response->json('attachmentUrl'))->not->toBeNull();

        $this->assertDatabaseHas('rma_messages', [
            'rma_id' => $data['rma']->id,
            'attachment' => 'zipper.png',
        ]);
    }

    /**
     * Test messages of another customer blocked.
     */
    public function test_messages_of_another_customer_blocked(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $data = $this->createReturn($owner);
        $other = $this->createCustomer();

        $response = $this->authenticatedGet($other, '/api/shop/return-messages?return_id='.$data['rma']->id);

        expect($response->getStatusCode())->toBeIn([403, 404]);
    }

    /**
     * Test return custom fields.
     */
    public function test_return_custom_fields(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $fields = $this->seedCustomFields();

        $response = $this->authenticatedGet($customer, '/api/shop/return-custom-fields');

        $response->assertOk();
        $rows = collect($response->json());
        expect($rows->firstWhere('id', $fields['text']->id)['isRequired'])->toBeTrue();
        expect($rows->firstWhere('id', $fields['select']->id)['options'][0]['value'])->toBe('morning');
    }

    /**
     * Test create accepts multipart with images.
     */
    public function test_create_accepts_multipart_with_images(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'default' => 1,
        ]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);
        $fields = $this->seedCustomFields();

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 1,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'package_condition' => 'packed',
            'agreement' => true,
            'custom_attributes' => [$fields['text']->id => 'INV-9921'],
            'images' => [UploadedFile::fake()->image('evidence.png')],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('packageCondition'))->toBe('packed');
        expect(count($response->json('images')))->toBe(1);
        expect($response->json('customAttributes.0.value'))->toBe('INV-9921');
        $this->assertDatabaseHas('rma_additional_fields', ['value' => 'INV-9921']);
    }

    /**
     * Test create rejects an unsupported image type.
     */
    public function test_create_rejects_an_unsupported_image_type(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'default' => 1,
        ]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 1,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'agreement' => true,
            'images' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
        $this->assertDatabaseMissing('rma', ['order_id' => $seed['order']->id]);
    }

    /**
     * Test create rejects an unknown package condition.
     */
    public function test_create_rejects_an_unknown_package_condition(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'default' => 1,
        ]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 1,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'package_condition' => 'opened',
            'agreement' => true,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
        $this->assertDatabaseMissing('rma', ['order_id' => $seed['order']->id]);
    }

    /**
     * Test create requires required custom fields.
     */
    public function test_create_requires_required_custom_fields(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'default' => 1,
        ]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);
        $this->seedCustomFields();

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 1,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'agreement' => true,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
        $this->assertDatabaseMissing('rma', ['order_id' => $seed['order']->id]);
    }

    /**
     * Test a canceled return releases the item quantity.
     */
    public function test_a_canceled_return_releases_the_item_quantity(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'default' => 1,
        ]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $rma = RMA::create([
            'order_id' => $seed['order']->id,
            'rma_status_id' => 1,
        ]);

        RMAItem::create([
            'rma_id' => $rma->id,
            'order_item_id' => $seed['orderItem']->id,
            'quantity' => 2,
            'resolution' => 'return',
        ]);

        $url = '/api/shop/returnable-items?order_id='.$seed['order']->id;

        $held = $this->authenticatedGet($customer, $url);
        expect((int) $held->json('0.currentQuantity'))->toBe(0);

        $rma->update(['rma_status_id' => 9]);

        $released = $this->authenticatedGet($customer, $url);
        expect((int) $released->json('0.currentQuantity'))->toBe(2);
        expect((int) $released->json('0.rmaQuantity'))->toBe(0);
    }

    /**
     * Test list carries the action flags.
     */
    public function test_list_carries_the_action_flags(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $this->createReturn($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/returns');

        $response->assertOk();
        $row = $response->json('0');
        expect($row['canClose'])->not->toBeNull();
        expect($row['canReopen'])->not->toBeNull();
        expect($row['isExpired'])->not->toBeNull();
        expect($row['images'])->toBeArray();
        expect($row['customAttributes'])->toBeArray();
    }

    /**
     * Create return.
     */
    private function createReturn($customer): array
    {
        $channel = Channel::first();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'RMA-SKU-1',
            'type' => 'simple',
            'name' => 'Returnable Product',
        ]);

        $status = RMAStatus::firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Pending',
                'status' => 1,
                'color' => '#facc15',
                'default' => 1,
            ]
        );

        $rma = RMA::create([
            'order_id' => $order->id,
            'rma_status_id' => $status->id,
            'information' => 'Item arrived damaged',
            'package_condition' => 'opened',
        ]);

        RMAItem::create([
            'rma_id' => $rma->id,
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'resolution' => 'return',
        ]);

        return compact('customer', 'order', 'orderItem', 'rma', 'status');
    }

    /**
     * Seed eligible order item.
     */
    private function seedEligibleOrderItem($customer): array
    {
        $channel = Channel::first();
        $product = Product::factory()->create();

        DB::table('product_flat')->insert([
            'product_id' => $product->id,
            'sku' => 'ELIGIBLE-1',
            'name' => 'Eligible Product',
            'url_key' => 'eligible-1-'.$product->id,
            'status' => 1,
            'visible_individually' => 1,
            'locale' => app()->getLocale(),
            'channel' => $channel->code,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'ELIGIBLE-1',
            'type' => 'simple',
            'name' => 'Eligible Product',
            'qty_ordered' => 2,
            'qty_invoiced' => 2,
            'qty_refunded' => 0,
            'qty_canceled' => 0,
            'rma_return_period' => 30,
        ]);

        return compact('order', 'orderItem', 'product');
    }

    /**
     * Seed custom fields.
     */
    private function seedCustomFields(): array
    {
        $text = RMACustomField::create([
            'status' => 1,
            'code' => 'invoice_number_'.uniqid(),
            'label' => 'Invoice number',
            'type' => 'text',
            'is_required' => 1,
            'position' => 1,
        ]);

        $select = RMACustomField::create([
            'status' => 1,
            'code' => 'pickup_slot_'.uniqid(),
            'label' => 'Preferred pickup slot',
            'type' => 'select',
            'is_required' => 0,
            'position' => 2,
        ]);

        RMACustomFieldOption::create([
            'rma_custom_field_id' => $select->id,
            'name' => 'Morning',
            'value' => 'morning',
        ]);

        return compact('text', 'select');
    }
}
