<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\CoreConfig;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\RMA\Models\RMA;
use Webkul\RMA\Models\RMAImage;
use Webkul\RMA\Models\RMAItem;
use Webkul\RMA\Models\RMAMessage;
use Webkul\RMA\Models\RMAStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class ReturnFileTest extends AdminApiTestCase
{
    /**
     * A one-pixel PNG, base64 encoded.
     */
    protected const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    /**
     * The permissions of a role that may work on return requests.
     */
    protected const REQUESTS_PERMISSIONS = ['sales', 'sales.rma', 'sales.rma.requests'];

    /**
     * Test an admin message attachment is stored on the private disk with core's path.
     */
    public function test_message_attachment_is_stored_on_the_private_disk(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $response = $this->sendMessageWithAttachment($admin, $data['rma']->id);

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $message = RMAMessage::query()->findOrFail((int) $response->json('id'));

        expect($message->attachment)->toBe('damage.png');
        expect($message->attachment_path)->toStartWith('rmas/'.$data['rma']->id.'/conversations/'.$message->id.'/');
        expect($message->attachment_path)->toEndWith('.png');

        Storage::disk('private')->assertExists($message->attachment_path);
        Storage::disk('public')->assertMissing($message->attachment_path);
        Storage::disk('public')->assertMissing('rma-conversation/'.$message->id);

        expect($response->json('attachmentUrl'))->toEndWith('/api/admin/rma/messages/'.$message->id.'/attachment');
    }

    /**
     * Test an uploaded attachment downloads with the same bytes under its original name.
     */
    public function test_uploaded_attachment_downloads_with_the_same_bytes(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $upload = $this->sendMessageWithAttachment($admin, $data['rma']->id);
        $messageId = (int) $upload->json('id');

        $response = $this->binaryGet($admin, '/api/admin/rma/messages/'.$messageId.'/attachment');

        $response->assertOk();
        expect($response->streamedContent())->toBe(base64_decode(self::PNG));
        expect((string) $response->headers->get('Content-Disposition'))->toContain('attachment');
        expect((string) $response->headers->get('Content-Disposition'))->toContain('damage.png');
    }

    /**
     * Test an attachment stored on the public disk by an earlier version still downloads.
     */
    public function test_attachment_stored_publicly_by_an_earlier_version_still_downloads(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $message = RMAMessage::query()->create([
            'rma_id' => $data['rma']->id,
            'message' => 'Old upload.',
            'is_admin' => 1,
        ]);

        $path = 'rma-conversation/'.$message->id.'/'.Str::random(40).'.png';

        Storage::disk(config('filesystems.default'))->put($path, base64_decode(self::PNG));

        $message->update([
            'attachment_path' => $path,
            'attachment' => 'old.png',
        ]);

        $response = $this->binaryGet($admin, '/api/admin/rma/messages/'.$message->id.'/attachment');

        $response->assertOk();
        expect($response->streamedContent())->toBe(base64_decode(self::PNG));
    }

    /**
     * Test a message without an attachment returns 404.
     */
    public function test_message_without_attachment_returns_404(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $message = RMAMessage::query()->create([
            'rma_id' => $data['rma']->id,
            'message' => 'No file.',
            'is_admin' => 1,
        ]);

        $this->binaryGet($admin, '/api/admin/rma/messages/'.$message->id.'/attachment')->assertStatus(404);
    }

    /**
     * Test an unknown message returns 404.
     */
    public function test_unknown_message_attachment_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->binaryGet($admin, '/api/admin/rma/messages/999999/attachment')->assertStatus(404);
    }

    /**
     * Test an attachment missing from every disk returns 404.
     */
    public function test_attachment_missing_from_every_disk_returns_404(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $message = RMAMessage::query()->create([
            'rma_id' => $data['rma']->id,
            'message' => 'Lost file.',
            'is_admin' => 1,
            'attachment_path' => 'rmas/'.$data['rma']->id.'/conversations/0/missing.png',
            'attachment' => 'missing.png',
        ]);

        $this->binaryGet($admin, '/api/admin/rma/messages/'.$message->id.'/attachment')->assertStatus(404);
    }

    /**
     * Test a return image streams inline with its stored bytes.
     */
    public function test_return_image_streams_inline(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();
        $image = $this->createImage($data['rma']);

        $response = $this->binaryGet($admin, '/api/admin/rma/requests/images/'.$image->id);

        $response->assertOk();
        expect($response->streamedContent())->toBe(base64_decode(self::PNG));
        expect((string) $response->headers->get('Content-Disposition'))->toContain('inline');
    }

    /**
     * Test the request detail points each image url at the image endpoint.
     */
    public function test_request_detail_points_image_urls_at_the_image_endpoint(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();
        $image = $this->createImage($data['rma']);

        $response = $this->adminGet($admin, '/api/admin/rma/requests/'.$data['rma']->id);

        $response->assertOk();
        $row = collect($response->json('images'))->firstWhere('id', $image->id);
        expect($row)->not->toBeNull();
        expect($row['path'])->toBe($image->path);
        expect($row['url'])->toEndWith('/api/admin/rma/requests/images/'.$image->id);
    }

    /**
     * Test an unknown image returns 404.
     */
    public function test_unknown_image_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->binaryGet($admin, '/api/admin/rma/requests/images/999999')->assertStatus(404);
    }

    /**
     * Test both downloads require authentication.
     */
    public function test_downloads_require_authentication(): void
    {
        $data = $this->createReturn();
        $image = $this->createImage($data['rma']);
        $message = $this->createMessageWithStoredAttachment($data['rma']);

        $this->get('/api/admin/rma/requests/images/'.$image->id, ['Accept' => 'application/octet-stream'])
            ->assertStatus(401);

        $this->get('/api/admin/rma/messages/'.$message->id.'/attachment', ['Accept' => 'application/octet-stream'])
            ->assertStatus(401);
    }

    /**
     * Test a role holding exactly the requests key can download both files.
     */
    public function test_role_with_the_requests_key_can_download(): void
    {
        $admin = $this->customRoleAdmin(self::REQUESTS_PERMISSIONS);
        $data = $this->createReturn();
        $image = $this->createImage($data['rma']);
        $message = $this->createMessageWithStoredAttachment($data['rma']);

        $this->binaryGet($admin, '/api/admin/rma/requests/images/'.$image->id)->assertOk();
        $this->binaryGet($admin, '/api/admin/rma/messages/'.$message->id.'/attachment')->assertOk();
    }

    /**
     * Test a role without the requests key is forbidden.
     */
    public function test_role_without_the_requests_key_is_forbidden(): void
    {
        $admin = $this->customRoleAdmin(['sales', 'sales.rma', 'sales.rma.reasons']);
        $data = $this->createReturn();
        $image = $this->createImage($data['rma']);
        $message = $this->createMessageWithStoredAttachment($data['rma']);

        $this->binaryGet($admin, '/api/admin/rma/requests/images/'.$image->id)->assertStatus(403);
        $this->binaryGet($admin, '/api/admin/rma/messages/'.$message->id.'/attachment')->assertStatus(403);
    }

    /**
     * Test an admin whose role is narrowed to fewer permissions is forbidden on the next request.
     */
    public function test_role_narrowed_to_fewer_permissions_is_forbidden(): void
    {
        $admin = $this->customRoleAdmin(array_merge(self::REQUESTS_PERMISSIONS, ['sales.orders']));
        $token = $this->tokenForNarrowedRole($admin, ['sales', 'sales.orders']);
        $data = $this->createReturn();
        $image = $this->createImage($data['rma']);
        $message = $this->createMessageWithStoredAttachment($data['rma']);

        $this->binaryGet($admin, '/api/admin/rma/requests/images/'.$image->id, $token)->assertStatus(403);
        $this->binaryGet($admin, '/api/admin/rma/messages/'.$message->id.'/attachment', $token)->assertStatus(403);
    }

    /**
     * Test a wrong role is refused before the image is looked up.
     */
    public function test_wrong_role_gets_403_for_an_unknown_image(): void
    {
        $admin = $this->customRoleAdmin(['sales', 'sales.rma', 'sales.rma.reasons']);

        $this->binaryGet($admin, '/api/admin/rma/requests/images/999999')->assertStatus(403);
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');
        Storage::fake('private');

        if (! in_array(config('filesystems.default'), ['public', 'private'], true)) {
            Storage::fake(config('filesystems.default'));
        }
    }

    /**
     * Send an admin message carrying a PNG attachment.
     */
    protected function sendMessageWithAttachment(Admin $admin, int $returnId): TestResponse
    {
        $this->allowPngAttachments();

        return $this->postJson('/api/admin/rma/messages', [
            'return_id' => $returnId,
            'message' => 'Photo of the damage.',
            'file' => UploadedFile::fake()->createWithContent('damage.png', base64_decode(self::PNG)),
        ], $this->adminHeaders($admin));
    }

    /**
     * Allow PNG files as RMA attachments in every scope.
     */
    protected function allowPngAttachments(): void
    {
        foreach ([null, Channel::first()?->code] as $channelCode) {
            CoreConfig::query()->updateOrCreate(
                [
                    'code' => 'sales.rma.setting.allowed_file_extension',
                    'channel_code' => $channelCode,
                    'locale_code' => null,
                ],
                ['value' => 'image/jpeg,image/png,image/webp']
            );
        }

        $this->forgetCoreConfigCache();
    }

    /**
     * Authenticated binary GET.
     */
    protected function binaryGet(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->get($url, array_merge(
            $this->adminHeaders($admin, $token),
            ['Accept' => 'application/octet-stream'],
        ));
    }

    /**
     * Store a PNG on the private disk as a return image.
     */
    protected function createImage(RMA $rma): RMAImage
    {
        $path = 'rmas/'.$rma->id.'/images/'.Str::random(40).'.png';

        Storage::disk('private')->put($path, base64_decode(self::PNG));

        return RMAImage::query()->create([
            'rma_id' => $rma->id,
            'path' => $path,
        ]);
    }

    /**
     * Create a message whose attachment is stored on the private disk.
     */
    protected function createMessageWithStoredAttachment(RMA $rma): RMAMessage
    {
        $message = RMAMessage::query()->create([
            'rma_id' => $rma->id,
            'message' => 'Attached.',
            'is_admin' => 1,
        ]);

        $path = 'rmas/'.$rma->id.'/conversations/'.$message->id.'/'.Str::random(40).'.png';

        Storage::disk('private')->put($path, base64_decode(self::PNG));

        $message->update([
            'attachment_path' => $path,
            'attachment' => 'attached.png',
        ]);

        return $message;
    }

    /**
     * Create an admin whose role holds only the given permissions.
     */
    protected function customRoleAdmin(array $permissions): Admin
    {
        $role = Role::create([
            'name' => 'rma-file-'.uniqid(),
            'description' => 'rma-file-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    /**
     * Give the admin a new role with exactly the given permissions and return a fresh access token.
     */
    protected function tokenForNarrowedRole(Admin $admin, array $permissions): string
    {
        $role = Role::create([
            'name' => 'narrowed-'.Str::random(6),
            'description' => 'narrowed',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        $admin->forceFill(['role_id' => $role->id])->save();

        return $this->adminAccessToken($admin->fresh());
    }

    /**
     * Create a return request on a completed order.
     */
    protected function createReturn(): array
    {
        $this->seedRequiredData();

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
