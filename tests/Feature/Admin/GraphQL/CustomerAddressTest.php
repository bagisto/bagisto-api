<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;

/**
 * GraphQL coverage for the customer-addresses sub-resource.
 */
class CustomerAddressTest extends AdminApiTestCase
{
    public function test_query_returns_customer_addresses(): void
    {
        $customerId = Customer::whereHas('addresses')->value('id');

        if ($customerId === null) {
            $this->markTestSkipped('No customer with addresses in the database.');
        }

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query addresses($customerId: Int!) {
              adminCustomerAddresses(customerId: $customerId) {
                edges { node { id firstName lastName city country } }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['customerId' => $customerId], $admin);

        $response->assertOk();
        $data = $response->json('data.adminCustomerAddresses');
        expect($data['totalCount'])->toBeInt()->toBeGreaterThan(0);
        expect($data['edges'])->toBeArray()->not->toBeEmpty();
    }

    public function test_query_requires_authentication(): void
    {
        $query = <<<'GQL'
            query { adminCustomerAddresses(customerId: 1) { totalCount } }
        GQL;

        $response = $this->adminGraphQL($query);

        expect($response->json('errors'))->not->toBeNull();
    }
}
