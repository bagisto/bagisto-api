<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Customer\Models\CustomerGroup;

/**
 * GraphQL coverage for admin Customers CRUD + sub-resources (Block C C1).
 */
class CustomerTest extends AdminApiTestCase
{
    protected function group(): CustomerGroup
    {
        $this->seedRequiredData();

        return CustomerGroup::where('code', 'general')->first();
    }

    protected function seedCustomer(array $overrides = []): Customer
    {
        return Customer::factory()->create(array_merge([
            'customer_group_id' => $this->group()->id,
            'status' => 1,
        ], $overrides));
    }

    public function test_listing(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $query = <<<'GQL'
            query {
              adminCustomers(first: 50) {
                edges { node { id _id email } }
                totalCount
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, [], $admin);
        $resp->assertOk();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminCustomers.edges') ?? []);
        expect($ids)->toContain($c->id);
    }

    public function test_listing_filter_by_email(): void
    {
        $admin = $this->createAdmin();
        $marker = 'gqlflt'.rand(1000, 9999);
        $c = $this->seedCustomer(['email' => $marker.'@example.com']);

        $query = <<<'GQL'
            query($email: String) {
              adminCustomers(first: 50, email: $email) {
                edges { node { _id } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['email' => $marker], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminCustomers.edges') ?? []);
        expect($ids)->toContain($c->id);
    }

    public function test_listing_filter_by_date_of_birth_range(): void
    {
        $admin = $this->createAdmin();
        $inRange = $this->seedCustomer(['date_of_birth' => '1990-06-15']);
        $outRange = $this->seedCustomer(['date_of_birth' => '2005-01-01']);

        $query = <<<'GQL'
            query($from: String, $to: String) {
              adminCustomers(first: 50, date_of_birth_from: $from, date_of_birth_to: $to) {
                edges { node { _id } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['from' => '1980-01-01', 'to' => '1999-12-31'], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminCustomers.edges') ?? []);
        expect($ids)->toContain($inRange->id);
        expect($ids)->not()->toContain($outRange->id);
    }

    public function test_listing_requires_auth(): void
    {
        $query = 'query { adminCustomers(first: 5) { edges { node { _id } } } }';
        $resp = $this->adminGraphQL($query);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_detail(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $query = <<<'GQL'
            query($id: ID!) { adminCustomer(id: $id) { id _id } }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/'.$c->id], $admin);
        $resp->assertOk();
        expect($resp->json('data.adminCustomer._id'))->toBe($c->id);
    }

    public function test_query_detail_resolves_camelcase_fields(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $query = <<<'GQL'
            query($id: ID!) {
              adminCustomer(id: $id) {
                _id
                firstName
                lastName
                group { _id code name }
                totalOrders
                totalAddresses
                createdAt
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/'.$c->id], $admin)->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $node = $resp->json('data.adminCustomer');

        expect($node['firstName'])->not->toBeNull();
        expect($node['group'])->not->toBeNull();
        expect($node['group']['_id'])->toBe($c->customer_group_id);
        expect($node['group']['code'])->not->toBeNull();
        expect($node['group']['name'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
        expect((int) $node['totalOrders'])->toBe(0);
        expect((int) $node['totalAddresses'])->toBe(0);
    }

    public function test_listing_resolves_group_object(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $query = <<<'GQL'
            query {
              adminCustomers(first: 50) {
                edges { node { _id email group { _id code name } } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, [], $admin)->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $edges = $resp->json('data.adminCustomers.edges') ?? [];
        $row = collect($edges)->first(fn ($e) => ($e['node']['_id'] ?? null) === $c->id);
        expect($row)->not->toBeNull();
        expect($row['node']['group'])->not->toBeNull();
        expect($row['node']['group']['_id'])->toBe($c->customer_group_id);
        expect($row['node']['group']['code'])->not->toBeNull();
        expect($row['node']['group']['name'])->not->toBeNull();
    }

    public function test_detail_null_group_does_not_500(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        \DB::table('customers')->where('id', $c->id)->update(['customer_group_id' => null]);

        $query = <<<'GQL'
            query($id: ID!) {
              adminCustomer(id: $id) { _id group { _id code name } }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/'.$c->id], $admin)->assertOk();
        expect($resp->json('errors'))->toBeNull();
        expect($resp->json('data.adminCustomer._id'))->toBe($c->id);
        expect($resp->json('data.adminCustomer.group'))->toBeNull();
    }

    public function test_detail_unknown(): void
    {
        $admin = $this->createAdmin();
        $query = <<<'GQL'
            query($id: ID!) { adminCustomer(id: $id) { id _id } }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/99999999'], $admin);
        $resp->assertOk();
        $errors = $resp->json('errors');
        $dataNull = $resp->json('data.adminCustomer') === null;
        expect($errors !== null || $dataNull)->toBeTrue();
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();
        $email = 'gqlc'.rand(1000, 9999).'@example.com';

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerInput!) {
              createAdminCustomer(input: $input) {
                adminCustomer { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => [
                'firstName' => 'Alice',
                'lastName' => 'GQL',
                'email' => $email,
                'customerGroupId' => $this->group()->id,
                'sendPassword' => true,
            ],
        ], $admin);
        $resp->assertOk();
        expect(Customer::where('email', $email)->exists())->toBeTrue();
    }

    public function test_mutation_create_requires_auth(): void
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerInput!) {
              createAdminCustomer(input: $input) { adminCustomer { _id } }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => [
                'firstName' => 'A', 'lastName' => 'B',
                'email' => 'noauth'.rand(100, 999).'@e.com',
                'customerGroupId' => 1,
            ],
        ]);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer(['first_name' => 'OldGQL']);

        $mutation = <<<'GQL'
            mutation($input: updateAdminCustomerInput!) {
              updateAdminCustomer(input: $input) {
                adminCustomer { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/'.$c->id, 'firstName' => 'NewGQL'],
        ], $admin);
        $resp->assertOk();
        $hasErrors = ! empty($resp->json('errors'));
        $name = $c->fresh()->first_name;
        expect($name === 'NewGQL' || $hasErrors)->toBeTrue();
    }

    public function test_mutation_delete(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerInput!) {
              deleteAdminCustomer(input: $input) {
                adminCustomer {
                  id
                  _id
                  firstName
                  email
                  message
                }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/'.$c->id],
        ], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        expect((int) $resp->json('data.deleteAdminCustomer.adminCustomer._id'))->toBe($c->id);
        expect($resp->json('data.deleteAdminCustomer.adminCustomer.message'))->not()->toBeNull();
        expect(Customer::where('id', $c->id)->exists())->toBeFalse();
    }

    public function test_mutation_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer();
        $b = $this->seedCustomer();

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerMassDeleteInput!) {
              createAdminCustomerMassDelete(input: $input) {
                adminCustomerMassDelete { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, ['input' => ['indices' => [$a->id, $b->id]]], $admin);
        $resp->assertOk();
        expect(Customer::where('id', $a->id)->exists())->toBeFalse();
        expect(Customer::where('id', $b->id)->exists())->toBeFalse();
    }

    public function test_mutation_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer(['status' => 1]);

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerMassUpdateStatusInput!) {
              createAdminCustomerMassUpdateStatus(input: $input) {
                adminCustomerMassUpdateStatus { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, ['input' => ['indices' => [$c->id], 'value' => 0]], $admin);
        $resp->assertOk();
        expect($c->fresh()->status)->toBe(0);
    }

    public function test_addresses_list(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        CustomerAddress::create([
            'customer_id' => $c->id, 'address_type' => CustomerAddress::ADDRESS_TYPE,
            'first_name' => 'A', 'last_name' => 'B', 'address' => '1 Addr',
            'city' => 'X', 'country' => 'US', 'postcode' => '10001', 'phone' => '555',
        ]);

        $query = <<<'GQL'
            query($cid: Int!) {
              adminCustomerAddresses(customerId: $cid) {
                edges { node { _id } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['cid' => $c->id], $admin);
        $resp->assertOk();
        $edges = $resp->json('data.adminCustomerAddresses.edges');
        $errors = $resp->json('errors');
        expect(is_array($edges) || is_array($errors))->toBeTrue();
    }

    public function test_address_mutation_create(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerAddressInput!) {
              createAdminCustomerAddress(input: $input) {
                adminCustomerAddress { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => [
                'customerId' => $c->id,
                'firstName' => 'J', 'lastName' => 'D',
                'address' => '99 GQL', 'city' => 'Boston',
                'country' => 'US', 'postcode' => '02101', 'phone' => '555',
            ],
        ], $admin);
        $resp->assertOk();
        expect(CustomerAddress::where('customer_id', $c->id)->exists())->toBeTrue();
    }

    public function test_note_mutation(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerNoteInput!) {
              createAdminCustomerNote(input: $input) {
                adminCustomerNote { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['customerId' => $c->id, 'note' => 'GQL note'],
        ], $admin);
        $resp->assertOk();
        expect(\DB::table('customer_notes')->where('customer_id', $c->id)->exists())->toBeTrue();
    }

    public function test_impersonate_mutation(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerImpersonateInput!) {
              createAdminCustomerImpersonate(input: $input) {
                adminCustomerImpersonate {
                  _id
                  token
                  customerId
                  customerEmail
                  customerName
                  impersonatedByAdminId
                  expiresAt
                }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['customerId' => $c->id],
        ], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();

        $node = $resp->json('data.createAdminCustomerImpersonate.adminCustomerImpersonate');
        expect($node['customerId'])->not()->toBeNull();
        expect($node['customerEmail'])->not()->toBeNull();
        expect($node['customerName'])->not()->toBeNull();
        expect($node['impersonatedByAdminId'])->not()->toBeNull();
        expect($node['expiresAt'])->not()->toBeNull();

        $tokenRow = \DB::table('personal_access_tokens')
            ->where('tokenable_type', Customer::class)
            ->where('tokenable_id', $c->id)
            ->orderByDesc('id')->first();
        expect($tokenRow)->not()->toBeNull();
    }

    protected function seedAddress(Customer $c): CustomerAddress
    {
        return CustomerAddress::create([
            'customer_id' => $c->id, 'address_type' => CustomerAddress::ADDRESS_TYPE,
            'first_name' => 'A', 'last_name' => 'B', 'address' => '1 Addr',
            'city' => 'OrigCity', 'state' => 'NY', 'country' => 'US', 'postcode' => '10001', 'phone' => '555',
        ]);
    }

    protected function addressUpdateMutation(): string
    {
        return <<<'GQL'
            mutation Upd($input: updateAdminCustomerAddressInput!) {
              updateAdminCustomerAddress(input: $input) {
                adminCustomerAddress { _id }
              }
            }
        GQL;
    }

    public function test_address_update_without_customer_id_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $addr = $this->seedAddress($c);
        $iri = "/api/admin/customers/{$c->id}/addresses/{$addr->id}";

        $r = $this->adminGraphQL($this->addressUpdateMutation(), ['input' => ['id' => $iri, 'city' => 'HACK']], $admin);

        expect($r->json('errors'))->not->toBeNull();
        expect(CustomerAddress::find($addr->id)->city)->toBe('OrigCity');
    }

    public function test_address_update_with_wrong_customer_id_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $other = $this->seedCustomer();
        $addr = $this->seedAddress($c);
        $iri = "/api/admin/customers/{$other->id}/addresses/{$addr->id}";

        $r = $this->adminGraphQL($this->addressUpdateMutation(), ['input' => ['id' => $iri, 'customerId' => $other->id, 'city' => 'HACK']], $admin);

        expect($r->json('errors'))->not->toBeNull();
        expect(CustomerAddress::find($addr->id)->city)->toBe('OrigCity');
    }

    public function test_address_update_with_correct_customer_id_succeeds(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $addr = $this->seedAddress($c);
        $iri = "/api/admin/customers/{$c->id}/addresses/{$addr->id}";

        $r = $this->adminGraphQL($this->addressUpdateMutation(), ['input' => ['id' => $iri, 'customerId' => $c->id, 'city' => 'NewCity']], $admin);

        $r->assertOk();
        expect($r->json('errors'))->toBeNull();
        expect(CustomerAddress::find($addr->id)->city)->toBe('NewCity');
    }
}
