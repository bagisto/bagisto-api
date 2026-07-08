<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;

class GdprRequestTest extends RestApiTestCase
{
    private function enableGdpr(): void
    {
        DB::table('core_config')->where('code', 'general.gdpr.settings.enabled')->delete();

        DB::table('core_config')->insert([
            'code' => 'general.gdpr.settings.enabled',
            'value' => '1',
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code' => core()->getRequestedLocaleCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function disableGdpr(): void
    {
        DB::table('core_config')->where('code', 'general.gdpr.settings.enabled')->delete();
    }

    private function seedRequest(Customer $customer, array $overrides = []): int
    {
        return (int) DB::table('gdpr_data_request')->insertGetId(array_merge([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'status' => 'pending',
            'type' => 'delete',
            'message' => 'Please remove my data',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_raise_request_happy_path(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/gdpr-requests', [
            'type' => 'delete',
            'message' => 'Please delete all my personal data.',
        ]);

        $response->assertStatus(201);
        expect($response->json('type'))->toBe('delete');
        expect($response->json('status'))->toBe('pending');
        expect($response->json('message'))->toBe('Please delete all my personal data.');
        expect($response->json('email'))->toBe($customer->email);

        $this->assertDatabaseHas('gdpr_data_request', [
            'customer_id' => $customer->id,
            'type' => 'delete',
            'status' => 'pending',
        ]);
    }

    public function test_raise_request_missing_type(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/gdpr-requests', [
            'message' => 'Some message',
        ]);

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_raise_request_invalid_type(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/gdpr-requests', [
            'type' => 'export',
            'message' => 'Some message',
        ]);

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_raise_request_missing_message(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/gdpr-requests', [
            'type' => 'update',
        ]);

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_list_returns_only_own_requests(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();

        $this->seedRequest($customer, ['type' => 'delete']);
        $this->seedRequest($customer, ['type' => 'update']);
        $otherId = $this->seedRequest($other);

        $response = $this->authenticatedGet($customer, '/api/shop/gdpr-requests');

        $response->assertOk();
        $data = $response->json('data') ?? $response->json();
        expect($data)->toBeArray();
        expect(count($data))->toBe(2);

        $ids = array_column($data, 'id');
        expect(in_array($otherId, $ids, true))->toBeFalse();
    }

    public function test_view_own_request(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/gdpr-requests/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('type'))->toBe('delete');
        expect($response->json('status'))->toBe('pending');
    }

    public function test_view_cross_customer_returns_404(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();
        $id = $this->seedRequest($other);

        $response = $this->authenticatedGet($customer, '/api/shop/gdpr-requests/'.$id);

        expect(in_array($response->getStatusCode(), [403, 404]))->toBeTrue();
    }

    public function test_revoke_own_request_happy_path(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer, ['status' => 'pending']);

        $response = $this->authenticatedPost($customer, '/api/shop/gdpr-requests/'.$id.'/revoke');

        $response->assertOk();
        expect($response->json('status'))->toBe('revoked');
        expect($response->json('revokedAt'))->not->toBeNull();

        $this->assertDatabaseHas('gdpr_data_request', [
            'id' => $id,
            'status' => 'revoked',
        ]);
    }

    public function test_revoke_approved_request_not_allowed(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer, ['status' => 'approved']);

        $response = $this->authenticatedPost($customer, '/api/shop/gdpr-requests/'.$id.'/revoke');

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_revoke_cross_customer_returns_404(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();
        $id = $this->seedRequest($other);

        $response = $this->authenticatedPost($customer, '/api/shop/gdpr-requests/'.$id.'/revoke');

        expect(in_array($response->getStatusCode(), [403, 404]))->toBeTrue();
    }

    public function test_delete_own_request_happy_path(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer);

        $response = $this->authenticatedDelete($customer, '/api/shop/gdpr-requests/'.$id);

        expect(in_array($response->getStatusCode(), [200, 204]))->toBeTrue();

        $this->assertDatabaseMissing('gdpr_data_request', ['id' => $id]);
    }

    public function test_delete_cross_customer_returns_404(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();
        $id = $this->seedRequest($other);

        $response = $this->authenticatedDelete($customer, '/api/shop/gdpr-requests/'.$id);

        expect(in_array($response->getStatusCode(), [403, 404]))->toBeTrue();
        $this->assertDatabaseHas('gdpr_data_request', ['id' => $id]);
    }

    public function test_raise_requires_authentication(): void
    {
        $this->enableGdpr();

        $response = $this->publicPost('/api/shop/gdpr-requests', [
            'type' => 'delete',
            'message' => 'msg',
        ]);

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_list_requires_authentication(): void
    {
        $this->enableGdpr();

        $response = $this->publicGet('/api/shop/gdpr-requests');

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_view_requires_authentication(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer);

        $response = $this->publicGet('/api/shop/gdpr-requests/'.$id);

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_revoke_requires_authentication(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer);

        $response = $this->publicPost('/api/shop/gdpr-requests/'.$id.'/revoke');

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_delete_requires_authentication(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer);

        $response = $this->publicDelete('/api/shop/gdpr-requests/'.$id);

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_raise_when_disabled_returns_disabled_error(): void
    {
        $this->disableGdpr();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/gdpr-requests', [
            'type' => 'delete',
            'message' => 'msg',
        ]);

        expect($response->getStatusCode())->toBe(400);
        expect(strtolower($response->getContent()))->toContain('disabled');
        expect(strtolower($response->getContent()))->toContain('admin');
    }

    public function test_list_when_disabled_returns_disabled_error(): void
    {
        $this->disableGdpr();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/gdpr-requests');

        expect($response->getStatusCode())->toBe(400);
        expect(strtolower($response->getContent()))->toContain('disabled');
    }
}
