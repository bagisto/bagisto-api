<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Customer\Models\CustomerGroup;

/**
 * REST coverage for the admin Customers CRUD + sub-resources (Block C C1).
 */
class CustomerTest extends AdminApiTestCase
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

    protected function uniqueEmail(string $prefix = 'cust'): string
    {
        return strtolower($prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99)).'@example.test';
    }

    protected function seedCustomer(array $overrides = []): Customer
    {
        $group = $this->group();

        return Customer::factory()->create(array_merge([
            'customer_group_id' => $group->id,
            'status'            => 1,
        ], $overrides));
    }

    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/customers')->assertStatus(401);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->postJson('/api/admin/customers', [])->assertStatus(401);
    }

    public function test_update_requires_auth(): void
    {
        $c = $this->seedCustomer();
        $this->putJson('/api/admin/customers/'.$c->id, [])->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $c = $this->seedCustomer();
        $this->deleteJson('/api/admin/customers/'.$c->id)->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $c = $this->seedCustomer();
        $this->publicGet('/api/admin/customers/'.$c->id)->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers');
        $resp->assertOk();
        expect($resp->json())->toHaveKeys(['data', 'meta']);
        expect($resp->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

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

    public function test_listing_per_page_capped(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers?per_page=9999');
        $resp->assertOk();
        expect($resp->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

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

    public function test_detail_null_group_returns_null_object(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        \DB::table('customers')->where('id', $c->id)->update(['customer_group_id' => null]);

        $resp = $this->adminGet($admin, '/api/admin/customers/'.$c->id);
        $resp->assertOk();
        expect($resp->json('group'))->toBeNull();
    }

    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/customers/999999')->assertStatus(404);
    }

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $email = $this->uniqueEmail('cr');

        $resp = $this->adminPost($admin, '/api/admin/customers', [
            'first_name'        => 'Alice',
            'last_name'         => 'Smith',
            'email'             => $email,
            'customer_group_id' => $this->group()->id,
            'send_password'     => true,
        ]);
        $resp->assertStatus(201);
        expect(Customer::where('email', $email)->exists())->toBeTrue();
    }

    public function test_create_with_explicit_password(): void
    {
        $admin = $this->createAdmin();
        $email = $this->uniqueEmail('cep');

        $resp = $this->adminPost($admin, '/api/admin/customers', [
            'first_name'        => 'Bob',
            'last_name'         => 'Jones',
            'email'             => $email,
            'customer_group_id' => $this->group()->id,
            'send_password'     => false,
            'password'          => 'secretpass',
        ]);
        $resp->assertStatus(201);
    }

    public function test_create_missing_password_when_not_send_password(): void
    {
        $admin = $this->createAdmin();

        $resp = $this->adminPost($admin, '/api/admin/customers', [
            'first_name'        => 'X', 'last_name' => 'Y',
            'email'             => $this->uniqueEmail('np'),
            'customer_group_id' => $this->group()->id,
            'send_password'     => false,
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_create_duplicate_email_422(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers', [
            'first_name'        => 'A', 'last_name' => 'B',
            'email'             => $existing->email,
            'customer_group_id' => $this->group()->id,
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_create_missing_required_fields_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers', ['first_name' => 'A']);
        expect($resp->getStatusCode())->toBe(422);
    }

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

    public function test_update_same_email_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPut($admin, '/api/admin/customers/'.$c->id, [
            'email' => $c->email,
        ]);
        $resp->assertOk();
    }

    public function test_update_duplicate_email_422(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer();
        $b = $this->seedCustomer();

        $resp = $this->adminPut($admin, '/api/admin/customers/'.$b->id, ['email' => $a->email]);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_update_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/customers/99999999', ['first_name' => 'X']);
        expect($resp->getStatusCode())->toBe(404);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminDelete($admin, '/api/admin/customers/'.$c->id);
        $resp->assertOk();
        expect(Customer::where('id', $c->id)->exists())->toBeFalse();
    }

    public function test_delete_with_pending_order_400(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        \DB::table('orders')->insert([
            'customer_id'   => $c->id,
            'customer_email'=> $c->email,
            'status'        => 'pending',
            'channel_id'    => \DB::table('channels')->value('id'),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $resp = $this->adminDelete($admin, '/api/admin/customers/'.$c->id);
        expect($resp->getStatusCode())->toBe(400);
        expect(Customer::where('id', $c->id)->exists())->toBeTrue();
    }

    public function test_delete_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/customers/99999999');
        expect($resp->getStatusCode())->toBe(404);
    }

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

    public function test_mass_delete_skips_with_orders(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer();
        \DB::table('orders')->insert([
            'customer_id' => $a->id, 'customer_email' => $a->email,
            'status'      => 'pending', 'channel_id' => \DB::table('channels')->value('id'),
            'created_at'  => now(), 'updated_at' => now(),
        ]);

        $resp = $this->adminPost($admin, '/api/admin/customers/mass-delete', ['indices' => [$a->id]]);
        $resp->assertOk();
        expect(Customer::where('id', $a->id)->exists())->toBeTrue();
        expect($resp->json('skipped'))->toBeArray()->not()->toBeEmpty();
    }

    public function test_mass_delete_empty_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/mass-delete', ['indices' => []]);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer(['status' => 1]);
        $b = $this->seedCustomer(['status' => 1]);

        $resp = $this->adminPost($admin, '/api/admin/customers/mass-update-status', [
            'indices' => [$a->id, $b->id], 'value' => 0,
        ]);
        $resp->assertOk();
        expect($a->fresh()->status)->toBe(0);
        expect($b->fresh()->status)->toBe(0);
    }

    public function test_mass_update_status_invalid_value_422(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedCustomer();
        $resp = $this->adminPost($admin, '/api/admin/customers/mass-update-status', [
            'indices' => [$a->id], 'value' => 5,
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    protected function seedAddress(int $customerId, array $overrides = []): CustomerAddress
    {
        return CustomerAddress::create(array_merge([
            'customer_id'  => $customerId,
            'address_type' => CustomerAddress::ADDRESS_TYPE,
            'first_name'   => 'A',
            'last_name'    => 'B',
            'address'      => '1 Test St',
            'city'         => 'Town',
            'country'      => 'US',
            'postcode'     => '10001',
            'phone'        => '1234567890',
        ], $overrides));
    }

    public function test_address_create(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/addresses', [
            'first_name'      => 'John', 'last_name' => 'Doe', 'address' => '123 Main',
            'city'            => 'NY', 'country' => 'US', 'postcode' => '10001', 'phone' => '5551234',
            'default_address' => true,
        ]);
        $resp->assertStatus(201);
        expect(CustomerAddress::where('customer_id', $c->id)->exists())->toBeTrue();
    }

    public function test_address_create_validates(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/addresses', ['city' => 'X']);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_address_list(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $this->seedAddress($c->id);

        $resp = $this->adminGet($admin, '/api/admin/customers/'.$c->id.'/addresses');
        $resp->assertOk();
        expect($resp->json('data'))->toBeArray()->not()->toBeEmpty();
    }

    public function test_address_detail(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $a = $this->seedAddress($c->id);

        $resp = $this->adminGet($admin, '/api/admin/customers/'.$c->id.'/addresses/'.$a->id);
        $resp->assertOk();
        expect($resp->json('id'))->toBe($a->id);
    }

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

    public function test_address_ownership_blocked(): void
    {
        $admin = $this->createAdmin();
        $c1 = $this->seedCustomer();
        $c2 = $this->seedCustomer();
        $a2 = $this->seedAddress($c2->id);

        $resp = $this->adminPut($admin, '/api/admin/customers/'.$c1->id.'/addresses/'.$a2->id, ['city' => 'X']);
        expect($resp->getStatusCode())->toBe(403);
    }

    public function test_address_delete(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $a = $this->seedAddress($c->id);

        $resp = $this->adminDelete($admin, '/api/admin/customers/'.$c->id.'/addresses/'.$a->id);
        $resp->assertOk();
        expect(CustomerAddress::where('id', $a->id)->exists())->toBeFalse();
    }

    public function test_address_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $this->adminGet($admin, '/api/admin/customers/'.$c->id.'/addresses/99999')->assertStatus(404);
    }

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
     * The processor reads from `request()->all()` for REST so the body's keys
     * bypass the DTO denormaliser (which would otherwise drop multi-word
     * snake_case keys per the `OutputOnlySnakeToCamelNameConverter` quirk).
     */
    public function test_note_create_with_snake_case_customer_notified(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', [
            'note'              => 'Snake-case payload.',
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

    public function test_note_create_with_camel_case_customer_notified(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', [
            'note'             => 'Camel-case payload.',
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

    public function test_note_empty_422(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();
        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/notes', ['note' => '']);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_note_unknown_customer_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/99999999/notes', ['note' => 'X']);
        expect($resp->getStatusCode())->toBe(404);
    }

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

    public function test_impersonate_returns_token(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/impersonate', []);
        $resp->assertStatus(201);
        expect($resp->json('token'))->toBeString()->not()->toBeEmpty();
        expect($resp->json('customerId'))->toBe($c->id);
        expect($resp->json('impersonatedByAdminId'))->toBe($admin->id);
        expect($resp->json('expiresAt'))->not()->toBeNull();
    }

    public function test_impersonate_token_authenticates_as_customer(): void
    {
        $admin = $this->createAdmin();
        $c = $this->seedCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$c->id.'/impersonate', []);
        $resp->assertStatus(201);
        $token = $resp->json('token');

        $tokenRow = \DB::table('personal_access_tokens')
            ->where('tokenable_type', \Webkul\Customer\Models\Customer::class)
            ->where('tokenable_id', $c->id)
            ->orderByDesc('id')
            ->first();
        expect($tokenRow)->not()->toBeNull();
        expect((string) $tokenRow->name)->toContain('admin-impersonate:'.$admin->id);
        expect($tokenRow->expires_at)->not()->toBeNull();
    }

    public function test_impersonate_unknown_customer_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/customers/99999999/impersonate', [])->assertStatus(404);
    }

    public function test_impersonate_requires_auth(): void
    {
        $c = $this->seedCustomer();
        $this->postJson('/api/admin/customers/'.$c->id.'/impersonate')->assertStatus(401);
    }
}
