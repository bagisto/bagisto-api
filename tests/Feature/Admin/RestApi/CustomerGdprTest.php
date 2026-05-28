<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\GDPR\Models\GDPRDataRequest;

/**
 * REST coverage for the admin Customer GDPR Requests endpoints (Block C C4).
 */
class CustomerGdprTest extends AdminApiTestCase
{
    protected function adminPut($admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete($admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function group(): CustomerGroup
    {
        $this->seedRequiredData();

        return CustomerGroup::where('code', 'general')->first();
    }

    protected function uniqueEmail(string $prefix = 'gdpr'): string
    {
        return strtolower($prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99)).'@example.test';
    }

    protected function seedCustomer(array $overrides = []): Customer
    {
        return Customer::factory()->create(array_merge([
            'customer_group_id' => $this->group()->id,
            'status'            => 1,
        ], $overrides));
    }

    protected function seedRequest(array $overrides = []): GDPRDataRequest
    {
        $this->seedRequiredData();

        $customer = $overrides['customer'] ?? null;
        unset($overrides['customer']);
        if (! $customer) {
            $customer = $this->seedCustomer();
        }

        return GDPRDataRequest::create(array_merge([
            'customer_id' => $customer->id,
            'email'       => $customer->email,
            'type'        => 'delete',
            'status'      => 'pending',
            'message'     => 'Please delete my account.',
        ], $overrides));
    }

    // ---------------------------------------------------------------- Auth

    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/customers/gdpr-requests')->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $r = $this->seedRequest();
        $this->publicGet('/api/admin/customers/gdpr-requests/'.$r->id)->assertStatus(401);
    }

    public function test_update_requires_auth(): void
    {
        $r = $this->seedRequest();
        $this->putJson('/api/admin/customers/gdpr-requests/'.$r->id, ['status' => 'processing'])->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $r = $this->seedRequest();
        $this->deleteJson('/api/admin/customers/gdpr-requests/'.$r->id)->assertStatus(401);
    }

    public function test_process_requires_auth(): void
    {
        $r = $this->seedRequest();
        $this->postJson('/api/admin/customers/gdpr-requests/'.$r->id.'/process')->assertStatus(401);
    }

    public function test_download_requires_auth(): void
    {
        $c = $this->seedCustomer();
        $this->postJson('/api/admin/customers/'.$c->id.'/gdpr-download-data')->assertStatus(401);
    }

    // ---------------------------------------------------------------- Listing

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->seedRequest();
        $this->seedRequest(['type' => 'update', 'status' => 'processing']);

        $response = $this->adminGet($admin, '/api/admin/customers/gdpr-requests');

        $response->assertOk();
        $body = $response->json();
        expect($body)->toHaveKeys(['data', 'meta']);
        expect($body['data'])->toBeArray();
        expect(count($body['data']))->toBeGreaterThanOrEqual(2);
        expect($body['meta'])->toHaveKey('total');
    }

    public function test_listing_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $this->seedRequest(['status' => 'pending']);
        $this->seedRequest(['status' => 'processing']);

        $response = $this->adminGet($admin, '/api/admin/customers/gdpr-requests?status=processing');

        $response->assertOk();
        $data = $response->json('data');
        expect($data)->toBeArray();
        foreach ($data as $row) {
            expect($row['status'])->toBe('processing');
        }
    }

    public function test_listing_filter_by_type(): void
    {
        $admin = $this->createAdmin();
        $this->seedRequest(['type' => 'delete']);
        $this->seedRequest(['type' => 'update']);

        $response = $this->adminGet($admin, '/api/admin/customers/gdpr-requests?type=update');

        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['type'])->toBe('update');
        }
    }

    public function test_listing_filter_by_customer_id(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->seedCustomer();
        $this->seedRequest(['customer' => $customer]);
        $this->seedRequest();

        $response = $this->adminGet($admin, '/api/admin/customers/gdpr-requests?customer_id='.$customer->id);

        $response->assertOk();
        $data = $response->json('data');
        expect(count($data))->toBeGreaterThanOrEqual(1);
        foreach ($data as $row) {
            expect($row['customerId'])->toBe($customer->id);
        }
    }

    // ---------------------------------------------------------------- Detail

    public function test_detail_returns_request(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest();

        $response = $this->adminGet($admin, '/api/admin/customers/gdpr-requests/'.$r->id);

        $response->assertOk();
        expect($response->json('id'))->toBe($r->id);
        expect($response->json('type'))->toBe('delete');
        expect($response->json('status'))->toBe('pending');
    }

    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/customers/gdpr-requests/9999999')->assertStatus(404);
    }

    // ---------------------------------------------------------------- Update

    public function test_update_status_success(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest(['status' => 'pending']);

        $response = $this->adminPut($admin, '/api/admin/customers/gdpr-requests/'.$r->id, [
            'status'  => 'processing',
            'message' => 'Looking into this.',
        ]);

        $response->assertOk();
        expect($response->json('status'))->toBe('processing');
        expect($response->json('message'))->toBe('Looking into this.');

        $fresh = GDPRDataRequest::find($r->id);
        expect($fresh->status)->toBe('processing');
    }

    public function test_update_invalid_status_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest();

        $this->adminPut($admin, '/api/admin/customers/gdpr-requests/'.$r->id, [
            'status' => 'nonsense',
        ])->assertStatus(422);
    }

    public function test_update_no_changes_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest();

        $this->adminPut($admin, '/api/admin/customers/gdpr-requests/'.$r->id, [])
            ->assertStatus(422);
    }

    public function test_update_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPut($admin, '/api/admin/customers/gdpr-requests/9999999', ['status' => 'processing'])
            ->assertStatus(404);
    }

    // ---------------------------------------------------------------- Delete

    public function test_delete_success(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest();

        $this->adminDelete($admin, '/api/admin/customers/gdpr-requests/'.$r->id)->assertOk();

        expect(GDPRDataRequest::find($r->id))->toBeNull();
    }

    public function test_delete_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminDelete($admin, '/api/admin/customers/gdpr-requests/9999999')->assertStatus(404);
    }

    // ---------------------------------------------------------------- Process

    public function test_process_delete_cascades_customer(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->seedCustomer();
        $r = $this->seedRequest(['customer' => $customer, 'type' => 'delete', 'status' => 'pending']);

        $response = $this->adminPost($admin, '/api/admin/customers/gdpr-requests/'.$r->id.'/process');

        $response->assertOk();
        expect($response->json('type'))->toBe('delete');
        expect($response->json('status'))->toBe('approved');
        expect($response->json('customerDeleted'))->toBeTrue();
        expect(Customer::find($customer->id))->toBeNull();
    }

    public function test_process_update_marks_approved_without_deletion(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->seedCustomer();
        $r = $this->seedRequest(['customer' => $customer, 'type' => 'update', 'status' => 'pending']);

        $response = $this->adminPost($admin, '/api/admin/customers/gdpr-requests/'.$r->id.'/process');

        $response->assertOk();
        expect($response->json('status'))->toBe('approved');
        expect($response->json('customerDeleted'))->toBeFalse();
        expect(Customer::find($customer->id))->not()->toBeNull();
    }

    public function test_process_already_approved_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest(['status' => 'approved']);

        $this->adminPost($admin, '/api/admin/customers/gdpr-requests/'.$r->id.'/process')
            ->assertStatus(422);
    }

    public function test_process_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/customers/gdpr-requests/9999999/process')->assertStatus(404);
    }

    // ---------------------------------------------------------------- Download data

    public function test_download_data_returns_expected_shape(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->seedCustomer();

        $response = $this->adminPost($admin, '/api/admin/customers/'.$customer->id.'/gdpr-download-data');

        $response->assertOk();
        expect($response->json('customerId'))->toBe($customer->id);
        expect($response->json('customerEmail'))->toBe($customer->email);
        $data = $response->json('data');
        expect($data)->toBeArray();
        expect($data)->toHaveKeys(['customer', 'addresses', 'orders', 'reviews', 'wishlist', 'notes']);
        // password must not be exposed
        expect($data['customer'])->not()->toHaveKey('password');
    }

    public function test_download_data_unknown_customer_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/customers/9999999/gdpr-download-data')->assertStatus(404);
    }
}
