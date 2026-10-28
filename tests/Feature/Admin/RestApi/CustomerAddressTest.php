<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class CustomerAddressTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test requires authentication.
     */
    public function test_requires_authentication(): void
    {
        $this->publicGet('/api/admin/customers/1/addresses')->assertStatus(401);
    }

    /**
     * Test unknown customer returns 404.
     */
    public function test_unknown_customer_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/customers/999999999/addresses')->assertStatus(404);
    }

    /**
     * Test returns data meta envelope of addresses.
     */
    public function test_returns_data_meta_envelope_of_addresses(): void
    {
        $customerId = $this->aCustomerWithAddresses();

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

    /**
     * Setting an address as default rejects a request without an admin token.
     */
    public function test_set_default_requires_authentication(): void
    {
        $this->publicPost('/api/admin/customers/1/addresses/1/set-default')->assertStatus(401);
    }

    /**
     * Setting an address as default makes it the only default address of the customer.
     */
    public function test_set_default_moves_the_default_flag(): void
    {
        [$customer, $first, $second] = $this->aCustomerWithTwoAddresses();

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/customers/'.$customer->id.'/addresses/'.$second->id.'/set-default');

        $response->assertOk();
        expect($response->json('id'))->toBe($second->id);
        expect($response->json('defaultAddress'))->toBeTrue();
        expect((int) CustomerAddress::find($second->id)->default_address)->toBe(1);
        expect((int) CustomerAddress::find($first->id)->default_address)->toBe(0);
    }

    /**
     * Setting as default an address that belongs to another customer is refused.
     */
    public function test_set_default_rejects_address_of_another_customer(): void
    {
        [, , $second] = $this->aCustomerWithTwoAddresses();
        $other = $this->createCustomer();

        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/customers/'.$other->id.'/addresses/'.$second->id.'/set-default')
            ->assertStatus(403);

        expect((int) CustomerAddress::find($second->id)->default_address)->toBe(0);
    }

    /**
     * Setting an unknown address as default returns 404.
     */
    public function test_set_default_unknown_address_returns_404(): void
    {
        $customer = $this->createCustomer();

        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/customers/'.$customer->id.'/addresses/999999999/set-default')
            ->assertStatus(404);
    }

    /**
     * Setting an address as default is refused for a role without the address edit permission.
     */
    public function test_set_default_without_permission_returns_403(): void
    {
        [$customer, $first, $second] = $this->aCustomerWithTwoAddresses();

        $admin = $this->createAdminWithoutAddressEdit();
        $this->adminPost($admin, '/api/admin/customers/'.$customer->id.'/addresses/'.$second->id.'/set-default', [], $this->adminAccessToken($admin))
            ->assertStatus(403);

        expect((int) CustomerAddress::find($first->id)->default_address)->toBe(1);
    }

    /**
     * Pick a customer that has at least one address; create one if missing.
     */
    protected function aCustomerWithAddresses(): int
    {
        return $this->bootstrapCustomerWithAddress();
    }

    /**
     * Create a fresh customer with two addresses, the first of them the default.
     */
    protected function aCustomerWithTwoAddresses(): array
    {
        $customer = $this->createCustomer();

        $first = CustomerAddress::factory()->create([
            'customer_id' => $customer->id,
            'address_type' => CustomerAddress::ADDRESS_TYPE,
            'default_address' => 1,
        ]);

        $second = CustomerAddress::factory()->create([
            'customer_id' => $customer->id,
            'address_type' => CustomerAddress::ADDRESS_TYPE,
            'default_address' => 0,
        ]);

        return [$customer, $first, $second];
    }

    /**
     * Create an admin whose role grants customer access but not address editing.
     */
    protected function createAdminWithoutAddressEdit(): Admin
    {
        $role = Role::create([
            'name' => 'NoAddressEdit '.uniqid(),
            'description' => 'no address edit',
            'permission_type' => 'custom',
            'permissions' => ['customers', 'customers.customers', 'customers.addresses'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }
}
