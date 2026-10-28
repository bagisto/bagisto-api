<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Customer\Models\CustomerGroup;

class CustomerTest extends AdminApiTestCase
{
    /**
     * Test listing requires auth.
     */
    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/customers')->assertStatus(401);
    }

    /**
     * Test create requires auth.
     */
    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->postJson('/api/admin/customers', [])->assertStatus(401);
    }

    /**
     * Test update requires auth.
     */
    public function test_update_requires_auth(): void
    {
        $c = $this->seedCustomer();
        $this->putJson('/api/admin/customers/'.$c->id, [])->assertStatus(401);
    }

    /**
     * Test delete requires auth.
     */
    public function test_delete_requires_auth(): void
    {
        $c = $this->seedCustomer();
        $this->deleteJson('/api/admin/customers/'.$c->id)->assertStatus(401);
    }

    /**
     * Test detail requires auth.
     */
    public function test_detail_requires_auth(): void
    {
        $c = $this->seedCustomer();
        $this->publicGet('/api/admin/customers/'.$c->id)->assertStatus(401);
    }

    /**
     * Test listing returns envelope.
     */
    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers');
        $resp->assertOk();
        expect($resp->json())->toHaveKeys(['data', 'meta']);
        expect($resp->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    /**
     * Test listing returns seeded row.
     */
    public function test_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminGet($admin, '/api/admin/customers?per_page=50');
        $resp->assertOk();
        $row = collect($resp->json('data'))->firstWhere('id', $c->id);
        expect($row)->not()->toBeNull();
        expect($row['email'])->toBe($c->email);
        expect($row)->toHaveKeys(['firstName', 'lastName', 'email', 'status', 'group']);
        expect($row)->not->toHaveKey('customerGroupId');
        expect($row)->not->toHaveKey('customerGroupName');
        expect($row['group'])->not()->toBeNull();
        expect($row['group'])->toMatchArray(['id' => $c->customer_group_id]);
        expect($row['group'])->toHaveKeys(['id', 'code', 'name']);
    }

    /**
     * Test listing filter by email.
     */
    public function test_listing_filter_by_email(): void
    {
        $admin = $this->createAdmin();
        $marker = 'flt'.rand(1000, 9999);
        $c = $this->seedCustomer(['email' => $marker.'@example.com']);

        $resp = $this->adminGet($admin, '/api/admin/customers?email='.$marker.'&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($c->id);
    }

    /**
     * Test listing filter by status.
     */
    public function test_listing_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $on = $this->seedCustomer(['status' => 1]);
        $off = $this->seedCustomer(['status' => 0]);

        $resp = $this->adminGet($admin, '/api/admin/customers?status=0&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($off->id);
        expect($ids)->not()->toContain($on->id);
    }

    /**
     * Test listing per page capped.
     */
    public function test_listing_per_page_capped(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers?per_page=9999');
        $resp->assertOk();
        expect($resp->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * Test detail returns customer.
     */
    public function test_detail_returns_customer(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer(['date_of_birth' => '1990-01-01']);

        $resp = $this->adminGet($admin, '/api/admin/customers/'.$c->id);
        $resp->assertOk();
        expect($resp->json('dateOfBirth'))->toBe('1990-01-01');
        expect($resp->json('id'))->toBe($c->id);
        expect($resp->json('email'))->toBe($c->email);
        expect($resp->json())->toHaveKeys(['totalAddresses', 'totalOrders', 'totalAmountSpent']);
        expect($resp->json())->not->toHaveKey('customerGroupId');
        expect($resp->json())->not->toHaveKey('customerGroupName');
        expect($resp->json('group'))->not()->toBeNull();
        expect($resp->json('group.id'))->toBe($c->customer_group_id);
        expect($resp->json('group.code'))->not()->toBeNull();
        expect($resp->json('group.name'))->not()->toBeNull();
    }

    /**
     * Test detail null group returns null object.
     */
    public function test_detail_null_group_returns_null_object(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        \DB::table('customers')->where('id', $c->id)->update(['customer_group_id' => null]);

        $resp = $this->adminGet($admin, '/api/admin/customers/'.$c->id);
        $resp->assertOk();
        expect($resp->json('group'))->toBeNull();
    }

    /**
     * Test detail unknown returns 404.
     */
    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/customers/999999')->assertStatus(404);
    }

    /**
     * Test create happy path.
     */
    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $email = $this->uniqueEmail('cr');

        $resp = $this->adminPost($admin, '/api/admin/customers', [
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => $email,
            'customer_group_id' => $this->group()->id,
            'send_password' => true,
        ]);
        $resp->assertStatus(201);
        expect(Customer::where('email', $email)->exists())->toBeTrue();
    }

    /**
     * Test create with explicit password.
     */
    public function test_create_with_explicit_password(): void
    {
        $admin = $this->createAdmin();
        $email = $this->uniqueEmail('cep');

        $resp = $this->adminPost($admin, '/api/admin/customers', [
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => $email,
            'customer_group_id' => $this->group()->id,
            'send_password' => false,
            'password' => 'secretpass',
        ]);
        $resp->assertStatus(201);
    }

    /**
     * Test create missing password when not send password.
     */
    public function test_create_missing_password_when_not_send_password(): void
    {
        $admin = $this->createAdmin();

        $resp = $this->adminPost($admin, '/api/admin/customers', [
            'first_name' => 'X', 'last_name' => 'Y',
            'email' => $this->uniqueEmail('np'),
            'customer_group_id' => $this->group()->id,
            'send_password' => false,
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test create duplicate email 422.
     */
    public function test_create_duplicate_email_422(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers', [
            'first_name' => 'A', 'last_name' => 'B',
            'email' => $existing->email,
            'customer_group_id' => $this->group()->id,
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test create missing required fields 422.
     */
    public function test_create_missing_required_fields_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers', ['first_name' => 'A']);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test update changes fields.
     */
    public function test_update_changes_fields(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer(['first_name' => 'Old']);

        $resp = $this->adminPut($admin, '/api/admin/customers/'.$c->id, [
            'first_name' => 'New',
        ]);
        $resp->assertOk();
        expect($c->fresh()->first_name)->toBe('New');
    }

    /**
     * Test update same email excludes self.
     */
    public function test_update_same_email_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPut($admin, '/api/admin/customers/'.$c->id, [
            'email' => $c->email,
        ]);
        $resp->assertOk();
    }

    /**
     * Test update duplicate email 422.
     */
    public function test_update_duplicate_email_422(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer();
        $b = $this->seedCustomer();

        $resp = $this->adminPut($admin, '/api/admin/customers/'.$b->id, ['email' => $a->email]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test update unknown 404.
     */
    public function test_update_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/customers/99999999', ['first_name' => 'X']);
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test delete happy path.
     */
    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminDelete($admin, '/api/admin/customers/'.$c->id);
        $resp->assertOk();
        expect(Customer::where('id', $c->id)->exists())->toBeFalse();
    }

    /**
     * Test delete with pending order 400.
     */
    public function test_delete_with_pending_order_400(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        \DB::table('orders')->insert([
            'customer_id' => $c->id,
            'increment_id' => 'ORD-'.uniqid(),
            'customer_email' => $c->email,
            'status' => 'pending',
            'channel_id' => \DB::table('channels')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resp = $this->adminDelete($admin, '/api/admin/customers/'.$c->id);
        expect($resp->getStatusCode())->toBe(400);
        expect(Customer::where('id', $c->id)->exists())->toBeTrue();
    }

    /**
     * Test delete unknown 404.
     */
    public function test_delete_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/customers/99999999');
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test mass delete happy path.
     */
    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer();
        $b = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/mass-delete', [
            'indices' => [$a->id, $b->id],
        ]);
        $resp->assertOk();
        expect(Customer::where('id', $a->id)->exists())->toBeFalse();
        expect(Customer::where('id', $b->id)->exists())->toBeFalse();
    }

    /**
     * Test mass delete skips with orders.
     */
    public function test_mass_delete_skips_with_orders(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer();
        \DB::table('orders')->insert([
            'customer_id' => $a->id, 'increment_id' => 'ORD-'.uniqid(), 'customer_email' => $a->email,
            'status' => 'pending', 'channel_id' => \DB::table('channels')->value('id'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $resp = $this->adminPost($admin, '/api/admin/customers/mass-delete', ['indices' => [$a->id]]);
        $resp->assertOk();
        expect(Customer::where('id', $a->id)->exists())->toBeTrue();
        expect($resp->json('skipped'))->toBeArray()->not()->toBeEmpty();
    }

    /**
     * Test mass delete empty 422.
     */
    public function test_mass_delete_empty_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/mass-delete', ['indices' => []]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test mass update status.
     */
    public function test_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer(['status' => 1]);
        $b = $this->seedCustomer(['status' => 1]);

        $resp = $this->adminPost($admin, '/api/admin/customers/mass-update-status', [
            'indices' => [$a->id, $b->id], 'value' => 0,
        ]);
        $resp->assertOk();
        expect((int) $a->fresh()->status)->toBe(0);
        expect((int) $b->fresh()->status)->toBe(0);
    }

    /**
     * Test mass update status invalid value 422.
     */
    public function test_mass_update_status_invalid_value_422(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer();
        $resp = $this->adminPost($admin, '/api/admin/customers/mass-update-status', [
            'indices' => [$a->id], 'value' => 5,
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test address create.
     */
    public function test_address_create(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/addresses', [
            'first_name' => 'John', 'last_name' => 'Doe', 'address' => '123 Main',
            'city' => 'NY', 'country' => 'US', 'postcode' => '10001', 'phone' => '5551234',
            'default_address' => true,
        ]);
        $resp->assertStatus(201);
        expect(CustomerAddress::where('customer_id', $c->id)->exists())->toBeTrue();
    }

    /**
     * Test address create validates.
     */
    public function test_address_create_validates(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/addresses', ['city' => 'X']);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test address list.
     */
    public function test_address_list(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $this->seedAddress($c->id);

        $resp = $this->adminGet($admin, '/api/admin/customers/'.$c->id.'/addresses');
        $resp->assertOk();
        expect($resp->json('data'))->toBeArray()->not()->toBeEmpty();
    }

    /**
     * Test address detail.
     */
    public function test_address_detail(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $a = $this->seedAddress($c->id);

        $resp = $this->adminGet($admin, '/api/admin/customers/'.$c->id.'/addresses/'.$a->id);
        $resp->assertOk();
        expect($resp->json('id'))->toBe($a->id);
    }

    /**
     * Test address update.
     */
    public function test_address_update(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $a = $this->seedAddress($c->id);

        $resp = $this->adminPut($admin, '/api/admin/customers/'.$c->id.'/addresses/'.$a->id, [
            'city' => 'Boston',
        ]);
        $resp->assertOk();
        expect($a->fresh()->city)->toBe('Boston');
    }

    /**
     * Test address ownership blocked.
     */
    public function test_address_ownership_blocked(): void
    {
        $admin = $this->createAdmin();
        $c1 = $this->seedCustomer();
        $c2 = $this->seedCustomer();
        $a2 = $this->seedAddress($c2->id);

        $resp = $this->adminPut($admin, '/api/admin/customers/'.$c1->id.'/addresses/'.$a2->id, ['city' => 'X']);
        expect($resp->getStatusCode())->toBe(403);
    }

    /**
     * Test address delete.
     */
    public function test_address_delete(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $a = $this->seedAddress($c->id);

        $resp = $this->adminDelete($admin, '/api/admin/customers/'.$c->id.'/addresses/'.$a->id);
        $resp->assertOk();
        expect(CustomerAddress::where('id', $a->id)->exists())->toBeFalse();
    }

    /**
     * Test address unknown 404.
     */
    public function test_address_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $this->adminGet($admin, '/api/admin/customers/'.$c->id.'/addresses/99999')->assertStatus(404);
    }

    /**
     * Test note create.
     */
    public function test_note_create(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', [
            'note' => 'High value customer.',
        ]);
        $resp->assertStatus(201);
        expect(\DB::table('customer_notes')->where('customer_id', $c->id)->exists())->toBeTrue();
    }

    /**
     * Regression — note POST must accept both `customer_notified` (snake_case,
     * what the OpenAPI block advertises) and `customerNotified` (camelCase).
     */
    public function test_note_create_with_snake_case_customer_notified(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', [
            'note' => 'Snake-case payload.',
            'customer_notified' => true,
        ]);
        expect($resp->getStatusCode())->not->toBe(500);
        $resp->assertStatus(201);
        expect($resp->json('customerNotified'))->toBeTrue();
        expect(\DB::table('customer_notes')
            ->where('customer_id', $c->id)
            ->where('note', 'Snake-case payload.')
            ->where('customer_notified', 1)
            ->exists())->toBeTrue();
    }

    /**
     * Test note create with camel case customer notified.
     */
    public function test_note_create_with_camel_case_customer_notified(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', [
            'note' => 'Camel-case payload.',
            'customerNotified' => true,
        ]);
        $resp->assertStatus(201);
        expect($resp->json('customerNotified'))->toBeTrue();
        expect(\DB::table('customer_notes')
            ->where('customer_id', $c->id)
            ->where('note', 'Camel-case payload.')
            ->where('customer_notified', 1)
            ->exists())->toBeTrue();
    }

    /**
     * Test note empty 422.
     */
    public function test_note_empty_422(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', ['note' => '']);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test note unknown customer 404.
     */
    public function test_note_unknown_customer_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/99999999/notes', ['note' => 'X']);
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test note append keeps existing.
     */
    public function test_note_append_keeps_existing(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', ['note' => 'First']);
        $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', ['note' => 'Second']);

        $rows = \DB::table('customer_notes')->where('customer_id', $c->id)->orderBy('id')->pluck('note')->all();
        expect($rows)->toContain('First');
        expect($rows)->toContain('Second');
    }

    /**
     * Admin put.
     */
    protected function adminPut($admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Admin delete.
     */
    protected function adminDelete($admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    /**
     * Group.
     */
    protected function group(): CustomerGroup
    {
        $this->seedRequiredData();

        return CustomerGroup::where('code', 'general')->first();
    }

    /**
     * Unique email.
     */
    protected function uniqueEmail(string $prefix = 'cust'): string
    {
        return strtolower($prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99)).'@example.test';
    }

    /**
     * Seed customer.
     */
    protected function seedCustomer(array $overrides = []): Customer
    {
        $group = $this->group();

        return Customer::factory()->create(array_merge([
            'customer_group_id' => $group->id,
            'status' => 1,
        ], $overrides));
    }

    /**
     * Seed address.
     */
    protected function seedAddress(int $customerId, array $overrides = []): CustomerAddress
    {
        return CustomerAddress::create(array_merge([
            'customer_id' => $customerId,
            'address_type' => CustomerAddress::ADDRESS_TYPE,
            'first_name' => 'A',
            'last_name' => 'B',
            'address' => '1 Test St',
            'city' => 'Town',
            'country' => 'US',
            'postcode' => '10001',
            'phone' => '1234567890',
        ], $overrides));
    }
}
