<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class TokenAbilityScopeTest extends AdminApiTestCase
{
    /**
     * Test custom token allows only its abilities.
     */
    public function test_custom_token_allows_only_its_abilities(): void
    {
        $admin = $this->roleAdmin(['sales', 'sales.invoices', 'sales.invoices.view', 'sales.shipments', 'sales.shipments.view']);
        $token = $this->customToken($admin, ['sales', 'sales.invoices', 'sales.invoices.view']);

        $this->adminGet($admin, '/api/admin/invoices', $token)->assertOk();
    }

    /**
     * Test custom token denies role permission not in token.
     */
    public function test_custom_token_denies_role_permission_not_in_token(): void
    {
        $admin = $this->roleAdmin(['sales', 'sales.invoices', 'sales.invoices.view', 'sales.shipments', 'sales.shipments.view']);
        $token = $this->customToken($admin, ['sales', 'sales.invoices', 'sales.invoices.view']);

        $this->adminGet($admin, '/api/admin/shipments', $token)->assertStatus(403);
    }

    /**
     * Test custom token cannot exceed its role.
     */
    public function test_custom_token_cannot_exceed_its_role(): void
    {
        $admin = $this->roleAdmin(['sales', 'sales.invoices', 'sales.invoices.view']);
        $token = $this->customToken($admin, ['sales', 'sales.invoices', 'sales.invoices.view', 'sales.shipments', 'sales.shipments.view']);

        $this->adminGet($admin, '/api/admin/shipments', $token)->assertStatus(403);
    }

    /**
     * Test same as web token follows the role.
     */
    public function test_same_as_web_token_follows_the_role(): void
    {
        $admin = $this->roleAdmin(['sales', 'sales.invoices', 'sales.invoices.view']);
        $token = $this->adminTokenSameAsWeb($admin);

        $this->adminGet($admin, '/api/admin/invoices', $token)->assertOk();
        $this->adminGet($admin, '/api/admin/shipments', $token)->assertStatus(403);
    }

    /**
     * Test all token on all role keeps full access.
     */
    public function test_all_token_on_all_role_keeps_full_access(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        $this->adminGet($admin, '/api/admin/invoices', $token)->assertOk();
        $this->adminGet($admin, '/api/admin/shipments', $token)->assertOk();
    }

    /**
     * Test custom token with wildcard is capped by its role.
     */
    public function test_custom_token_with_wildcard_is_capped_by_its_role(): void
    {
        $admin = $this->roleAdmin(['sales', 'sales.invoices', 'sales.invoices.view']);
        $token = $this->customToken($admin, ['*']);

        $this->adminGet($admin, '/api/admin/invoices', $token)->assertOk();
        $this->adminGet($admin, '/api/admin/shipments', $token)->assertStatus(403);
    }

    /**
     * Test token ip allowlist does not admit unlisted loopback.
     */
    public function test_token_ip_allowlist_does_not_admit_unlisted_loopback(): void
    {
        $admin = $this->createAdmin();
        $plain = Str::random(40);

        $row = AdminPersonalAccessToken::create([
            'admin_id' => $admin->id,
            'name' => 'ip-'.Str::random(6),
            'token' => hash('sha256', $plain),
            'token_preview' => substr($plain, 0, 8),
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_ALL,
            'abilities' => [],
            'expires_at' => now()->addDay(),
            'status' => AdminPersonalAccessToken::STATUS_ACTIVE,
            'allowed_ips' => ['10.0.0.5'],
        ]);

        $this->assertFalse($row->isIpAllowed('127.0.0.1'));

        $this->call('GET', '/api/admin/get', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$row->id.'|'.$plain,
            'HTTP_ACCEPT' => 'application/json',
            'REMOTE_ADDR' => '127.0.0.1',
        ])->assertStatus(401);
    }

    /**
     * Custom token.
     */
    private function customToken(object $admin, array $abilities): string
    {
        $plain = Str::random(40);

        $row = AdminPersonalAccessToken::create([
            'admin_id' => $admin->id,
            'name' => 'scope-'.Str::random(6),
            'token' => hash('sha256', $plain),
            'token_preview' => substr($plain, 0, 8),
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_CUSTOM,
            'abilities' => $abilities,
            'rate_limit_per_minute' => null,
            'rate_limit_per_day' => null,
            'expires_at' => now()->addDay(),
            'status' => AdminPersonalAccessToken::STATUS_ACTIVE,
            'created_by_admin_id' => $admin->id,
        ]);

        return $row->id.'|'.$plain;
    }

    /**
     * Role admin.
     */
    private function roleAdmin(array $permissions): object
    {
        $role = Role::create([
            'name' => 'r-'.Str::random(6),
            'description' => 'test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }
}
