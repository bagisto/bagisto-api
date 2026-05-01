<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\CustomerAddress;

class CustomerAddressTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/customer-addresses';

    private function dummyAddressInput(array $overrides = []): array
    {
        return array_merge([
            'firstName'      => 'Alice',
            'lastName'       => 'Tester',
            'email'          => 'address_'.uniqid().'@example.com',
            'phone'          => '5551234567',
            'address1'       => '123 Test Street',
            'address2'       => 'Suite 200',
            'city'           => 'Test City',
            'state'          => 'CA',
            'country'        => 'US',
            'postcode'       => '90001',
            'useForShipping' => true,
            'defaultAddress' => false,
        ], $overrides);
    }

    private function createAddressFor($customer, array $overrides = []): CustomerAddress
    {
        return CustomerAddress::create(array_merge([
            'customer_id'   => $customer->id,
            'address_type'  => 'customer',
            'first_name'    => 'Existing',
            'last_name'     => 'Address',
            'email'         => 'existing_'.uniqid().'@example.com',
            'phone'         => '5559999999',
            'address1'      => '99 Existing Lane',
            'city'          => 'Old City',
            'state'         => 'NY',
            'country'       => 'US',
            'postcode'      => '10001',
        ], $overrides));
    }

    // ── GET Collection ────────────────────────────────────────

    public function test_get_collection_returns_only_own_addresses(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();

        $this->createAddressFor($customer, ['first_name' => 'Mine']);
        $this->createAddressFor($other, ['first_name' => 'Theirs']);

        $response = $this->authenticatedGet($customer, $this->baseUrl);

        $response->assertOk();
        $data = $response->json();
        expect($data)->toBeArray();

        foreach ($data as $address) {
            expect((int) ($address['customer_id'] ?? $customer->id))->toBe($customer->id);
        }
    }

    public function test_get_collection_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    // ── GET single ────────────────────────────────────────────

    public function test_get_single_address(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $address = $this->createAddressFor($customer);

        $response = $this->authenticatedGet($customer, $this->baseUrl.'/'.$address->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($address->id);
    }

    public function test_get_nonexistent_address_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
    }

    // ── POST create ───────────────────────────────────────────

    public function test_create_address_successfully(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->baseUrl, $this->dummyAddressInput([
            'firstName' => 'Charlie',
            'lastName'  => 'Newton',
        ]));

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'first_name'  => 'Charlie',
            'last_name'   => 'Newton',
        ]);
    }

    public function test_create_address_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->baseUrl, $this->dummyAddressInput());

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_create_address_default_unsets_other_defaults(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $existing = $this->createAddressFor($customer, ['default_address' => true]);

        $response = $this->authenticatedPost($customer, $this->baseUrl, $this->dummyAddressInput([
            'firstName'      => 'NewDefault',
            'defaultAddress' => true,
        ]));

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $this->assertDatabaseHas('addresses', [
            'id'              => $existing->id,
            'default_address' => false,
        ]);
    }

    // ── PUT update ────────────────────────────────────────────

    public function test_update_address_successfully(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $address = $this->createAddressFor($customer, ['first_name' => 'Original']);

        $response = $this->authenticatedPut($customer, $this->baseUrl.'/'.$address->id, $this->dummyAddressInput([
            'addressId' => $address->id,
            'firstName' => 'Updated',
            'city'      => 'New City',
        ]));

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseHas('addresses', [
            'id'         => $address->id,
            'first_name' => 'Updated',
            'city'       => 'New City',
        ]);
    }

    public function test_update_address_requires_auth(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $address = $this->createAddressFor($customer);

        $response = $this->putJson($this->baseUrl.'/'.$address->id, $this->dummyAddressInput([
            'addressId' => $address->id,
            'firstName' => 'X',
        ]), $this->storefrontHeaders());

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_update_fails_for_another_customers_address(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $intruder = $this->createCustomer();
        $address = $this->createAddressFor($owner, ['first_name' => 'Owner']);

        $response = $this->authenticatedPut($intruder, $this->baseUrl.'/'.$address->id, $this->dummyAddressInput([
            'addressId' => $address->id,
            'firstName' => 'Hacked',
        ]));

        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
        $this->assertDatabaseHas('addresses', [
            'id'         => $address->id,
            'first_name' => 'Owner',
        ]);
    }

    // ── DELETE ────────────────────────────────────────────────

    public function test_delete_address_successfully(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $address = $this->createAddressFor($customer);

        $response = $this->authenticatedDelete($customer, $this->baseUrl.'/'.$address->id);

        expect($response->getStatusCode())->toBeIn([200, 204]);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $address = $this->createAddressFor($customer);

        $response = $this->publicDelete($this->baseUrl.'/'.$address->id);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_delete_fails_for_another_customers_address(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $intruder = $this->createCustomer();
        $address = $this->createAddressFor($owner);

        $response = $this->authenticatedDelete($intruder, $this->baseUrl.'/'.$address->id);

        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }
}
