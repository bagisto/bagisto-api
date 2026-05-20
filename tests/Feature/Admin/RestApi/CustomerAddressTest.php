<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;

/**
 * REST coverage for the Create-Order screen's "saved addresses" sub-resource —
 * GET /api/admin/customers/{id}/addresses.
 */
class CustomerAddressTest extends AdminApiTestCase
{
    /** Pick a customer that has at least one address, or return null. */
    protected function aCustomerWithAddresses(): ?int
    {
        return Customer::whereHas('addresses')->value('id');
    }

    public function test_requires_authentication(): void
    {
        $this->publicGet('/api/admin/customers/1/addresses')->assertStatus(401);
    }

    public function test_unknown_customer_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/customers/999999999/addresses')->assertStatus(404);
    }

    public function test_returns_data_meta_envelope_of_addresses(): void
    {
        $customerId = $this->aCustomerWithAddresses();

        if ($customerId === null) {
            $this->markTestSkipped('No customer with addresses in the database.');
        }

        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/customers/'.$customerId.'/addresses');

        $response->assertOk();
        expect($response->json())->toHaveKeys(['data', 'meta']);
        expect($response->json('data'))->toBeArray()->not->toBeEmpty();
        expect($response->json('data.0'))->toHaveKeys([
            'id', 'addressType', 'firstName', 'lastName', 'address',
            'city', 'state', 'country', 'postcode',
        ]);
    }
}
