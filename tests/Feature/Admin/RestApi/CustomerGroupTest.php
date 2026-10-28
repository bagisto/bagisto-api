<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;

class CustomerGroupTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test listing requires auth.
     */
    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/customers/groups')->assertStatus(401);
    }

    /**
     * Test create requires auth.
     */
    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->postJson('/api/admin/customers/groups', [])->assertStatus(401);
    }

    /**
     * Test update requires auth.
     */
    public function test_update_requires_auth(): void
    {
        $g = $this->seedUserGroup();
        $this->putJson('/api/admin/customers/groups/'.$g->id, [])->assertStatus(401);
    }

    /**
     * Test delete requires auth.
     */
    public function test_delete_requires_auth(): void
    {
        $g = $this->seedUserGroup();
        $this->deleteJson('/api/admin/customers/groups/'.$g->id)->assertStatus(401);
    }

    /**
     * Test detail requires auth.
     */
    public function test_detail_requires_auth(): void
    {
        $g = $this->seedUserGroup();
        $this->publicGet('/api/admin/customers/groups/'.$g->id)->assertStatus(401);
    }

    /**
     * Test listing returns envelope.
     */
    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers/groups');
        $resp->assertOk();
        expect($resp->json())->toHaveKeys(['data', 'meta']);
        expect($resp->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    /**
     * Test listing includes seeded group.
     */
    public function test_listing_includes_seeded_group(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();

        $resp = $this->adminGet($admin, '/api/admin/customers/groups?per_page=50');
        $resp->assertOk();
        $row = collect($resp->json('data'))->firstWhere('id', $g->id);
        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['code', 'name', 'isUserDefined']);
        expect($row['code'])->toBe($g->code);
    }

    /**
     * Test listing filter by code.
     */
    public function test_listing_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $marker = $this->uniqueCode('flt');
        $g = $this->seedUserGroup(['code' => $marker]);

        $resp = $this->adminGet($admin, '/api/admin/customers/groups?code='.$marker.'&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($g->id);
    }

    /**
     * Test listing filter by name.
     */
    public function test_listing_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup(['name' => 'ZzMarkerName'.rand(100, 999)]);

        $resp = $this->adminGet($admin, '/api/admin/customers/groups?name=ZzMarkerName&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($g->id);
    }

    /**
     * Test listing filter by is user defined.
     */
    public function test_listing_filter_by_is_user_defined(): void
    {
        $admin = $this->createAdmin();
        $u = $this->seedUserGroup();
        $sys = $this->systemGroup();

        $resp = $this->adminGet($admin, '/api/admin/customers/groups?is_user_defined=0&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        if ($sys) {
            expect($ids)->toContain($sys->id);
        }
        expect($ids)->not()->toContain($u->id);
    }

    /**
     * Test listing per page capped.
     */
    public function test_listing_per_page_capped(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers/groups?per_page=9999');
        $resp->assertOk();
        expect($resp->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * Test listing sort by code.
     */
    public function test_listing_sort_by_code(): void
    {
        $admin = $this->createAdmin();
        $this->seedUserGroup(['code' => 'aaa'.rand(100, 999)]);

        $resp = $this->adminGet($admin, '/api/admin/customers/groups?sort=code&order=asc&per_page=50');
        $resp->assertOk();
    }

    /**
     * Test detail returns group.
     */
    public function test_detail_returns_group(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();

        $resp = $this->adminGet($admin, '/api/admin/customers/groups/'.$g->id);
        $resp->assertOk();
        expect($resp->json('id'))->toBe($g->id);
        expect($resp->json('code'))->toBe($g->code);
        expect($resp->json())->toHaveKeys(['customersCount']);
    }

    /**
     * Test detail unknown returns 404.
     */
    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/customers/groups/99999999')->assertStatus(404);
    }

    /**
     * Test create happy path.
     */
    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('cr');

        $resp = $this->adminPost($admin, '/api/admin/customers/groups', [
            'code' => $code, 'name' => 'Brand New',
        ]);
        $resp->assertStatus(201);
        $row = CustomerGroup::where('code', $code)->first();
        expect($row)->not()->toBeNull();
        expect((int) $row->is_user_defined)->toBe(1);
    }

    /**
     * Test create missing required 422.
     */
    public function test_create_missing_required_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/groups', ['name' => 'No Code']);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test create duplicate code 422.
     */
    public function test_create_duplicate_code_422(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();
        $resp = $this->adminPost($admin, '/api/admin/customers/groups', [
            'code' => $g->code, 'name' => 'Dup',
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test create invalid code rule 422.
     */
    public function test_create_invalid_code_rule_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/groups', [
            'code' => '123-not-valid', 'name' => 'Bad',
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test update changes name.
     */
    public function test_update_changes_name(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup(['name' => 'Old']);

        $resp = $this->adminPut($admin, '/api/admin/customers/groups/'.$g->id, [
            'name' => 'New',
        ]);
        $resp->assertOk();
        expect($g->fresh()->name)->toBe('New');
    }

    /**
     * Test update changes code.
     */
    public function test_update_changes_code(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();
        $newCode = $this->uniqueCode('upd');

        $resp = $this->adminPut($admin, '/api/admin/customers/groups/'.$g->id, [
            'code' => $newCode,
        ]);
        $resp->assertOk();
        expect($g->fresh()->code)->toBe($newCode);
    }

    /**
     * Test update same code excludes self.
     */
    public function test_update_same_code_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();

        $resp = $this->adminPut($admin, '/api/admin/customers/groups/'.$g->id, [
            'code' => $g->code, 'name' => $g->name,
        ]);
        $resp->assertOk();
    }

    /**
     * Test update duplicate code 422.
     */
    public function test_update_duplicate_code_422(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedUserGroup();
        $b = $this->seedUserGroup();

        $resp = $this->adminPut($admin, '/api/admin/customers/groups/'.$b->id, [
            'code' => $a->code,
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test update unknown 404.
     */
    public function test_update_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/customers/groups/99999999', ['name' => 'X']);
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test update system group code blocked.
     */
    public function test_update_system_group_code_blocked(): void
    {
        $admin = $this->createAdmin();
        $sys = $this->systemGroup();

        $resp = $this->adminPut($admin, '/api/admin/customers/groups/'.$sys->id, [
            'code' => 'changed_sys_code',
        ]);
        expect($resp->getStatusCode())->toBe(422);
        expect($sys->fresh()->code)->toBe($sys->code);
    }

    /**
     * Test update system group name allowed.
     */
    public function test_update_system_group_name_allowed(): void
    {
        $admin = $this->createAdmin();
        $sys = $this->systemGroup();

        $oldName = $sys->name;
        $resp = $this->adminPut($admin, '/api/admin/customers/groups/'.$sys->id, [
            'name' => 'Renamed System',
        ]);
        $resp->assertOk();
        expect($sys->fresh()->name)->toBe('Renamed System');

        $sys->update(['name' => $oldName]);
    }

    /**
     * Test delete happy path.
     */
    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();

        $resp = $this->adminDelete($admin, '/api/admin/customers/groups/'.$g->id);
        $resp->assertOk();
        expect(CustomerGroup::where('id', $g->id)->exists())->toBeFalse();
    }

    /**
     * Test delete system group 400.
     */
    public function test_delete_system_group_400(): void
    {
        $admin = $this->createAdmin();
        $sys = $this->systemGroup();

        $resp = $this->adminDelete($admin, '/api/admin/customers/groups/'.$sys->id);
        expect($resp->getStatusCode())->toBe(400);
        expect(CustomerGroup::where('id', $sys->id)->exists())->toBeTrue();
    }

    /**
     * Test delete group with customers 400.
     */
    public function test_delete_group_with_customers_400(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();

        Customer::factory()->create([
            'customer_group_id' => $g->id,
            'status' => 1,
        ]);

        $resp = $this->adminDelete($admin, '/api/admin/customers/groups/'.$g->id);
        expect($resp->getStatusCode())->toBe(400);
        expect(CustomerGroup::where('id', $g->id)->exists())->toBeTrue();
    }

    /**
     * Test delete unknown 404.
     */
    public function test_delete_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/customers/groups/99999999');
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test mass delete happy path.
     */
    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedUserGroup();
        $b = $this->seedUserGroup();

        $resp = $this->adminPost($admin, '/api/admin/customers/groups/mass-delete', [
            'indices' => [$a->id, $b->id],
        ]);
        $resp->assertOk();
        expect(CustomerGroup::where('id', $a->id)->exists())->toBeFalse();
        expect(CustomerGroup::where('id', $b->id)->exists())->toBeFalse();
    }

    /**
     * Test mass delete skips system group.
     */
    public function test_mass_delete_skips_system_group(): void
    {
        $admin = $this->createAdmin();
        $sys = $this->systemGroup();
        $u = $this->seedUserGroup();

        $resp = $this->adminPost($admin, '/api/admin/customers/groups/mass-delete', [
            'indices' => [$sys->id, $u->id],
        ]);
        $resp->assertOk();
        expect(CustomerGroup::where('id', $sys->id)->exists())->toBeTrue();
        expect(CustomerGroup::where('id', $u->id)->exists())->toBeFalse();
        expect($resp->json('skipped'))->toBeArray()->not()->toBeEmpty();
        expect($resp->json('deleted'))->toContain($u->id);
    }

    /**
     * Test mass delete skips group with customers.
     */
    public function test_mass_delete_skips_group_with_customers(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();
        Customer::factory()->create([
            'customer_group_id' => $g->id, 'status' => 1,
        ]);

        $resp = $this->adminPost($admin, '/api/admin/customers/groups/mass-delete', [
            'indices' => [$g->id],
        ]);
        $resp->assertOk();
        expect(CustomerGroup::where('id', $g->id)->exists())->toBeTrue();
        expect($resp->json('skipped'))->toBeArray()->not()->toBeEmpty();
    }

    /**
     * Test mass delete empty 422.
     */
    public function test_mass_delete_empty_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/groups/mass-delete', ['indices' => []]);
        expect($resp->getStatusCode())->toBe(422);
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
     * Unique code.
     */
    protected function uniqueCode(string $prefix = 'grp'): string
    {
        return $prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99);
    }

    /**
     * Seed user group.
     */
    protected function seedUserGroup(array $overrides = []): CustomerGroup
    {
        $this->seedRequiredData();

        return CustomerGroup::create(array_merge([
            'code' => $this->uniqueCode('u'),
            'name' => 'User Group',
            'is_user_defined' => 1,
        ], $overrides));
    }

    /**
     * System group.
     */
    protected function systemGroup(): CustomerGroup
    {
        return $this->findOrCreateSystemCustomerGroup();
    }
}
