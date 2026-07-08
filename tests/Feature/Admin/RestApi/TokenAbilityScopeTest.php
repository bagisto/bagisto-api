<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

/**
 * The admin integration token's permission_type must be enforced at every
 * endpoint — a `custom` token is restricted to its frozen abilities even when
 * the owner's role grants more. Regression for the bug where every permission
 * check read the role directly and ignored the token entirely.
 */
class TokenAbilityScopeTest extends AdminApiTestCase
{
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

    public function test_custom_token_allows_only_its_abilities(): void
    {
        $admin = $this->roleAdmin(['sales.invoices.view', 'sales.shipments.view']);
        $token = $this->customToken($admin, ['sales.invoices.view']);

        $this->adminGet($admin, '/api/admin/invoices', $token)->assertOk();
    }

    public function test_custom_token_denies_role_permission_not_in_token(): void
    {
        // Role grants shipments, but the token's abilities do NOT — must be 403.
        $admin = $this->roleAdmin(['sales.invoices.view', 'sales.shipments.view']);
        $token = $this->customToken($admin, ['sales.invoices.view']);

        $this->adminGet($admin, '/api/admin/shipments', $token)->assertStatus(403);
    }

    public function test_custom_token_cannot_exceed_its_role(): void
    {
        // Token lists shipments but the role does not grant it — capped to the role.
        $admin = $this->roleAdmin(['sales.invoices.view']);
        $token = $this->customToken($admin, ['sales.invoices.view', 'sales.shipments.view']);

        $this->adminGet($admin, '/api/admin/shipments', $token)->assertStatus(403);
    }

    public function test_same_as_web_token_follows_the_role(): void
    {
        $admin = $this->roleAdmin(['sales.invoices.view']);
        $token = $this->adminTokenSameAsWeb($admin);

        $this->adminGet($admin, '/api/admin/invoices', $token)->assertOk();
        $this->adminGet($admin, '/api/admin/shipments', $token)->assertStatus(403);
    }

    public function test_all_token_on_all_role_keeps_full_access(): void
    {
        $admin = $this->createAdmin(); // default role_id = 1 (all access)
        $token = $this->adminToken($admin);

        $this->adminGet($admin, '/api/admin/invoices', $token)->assertOk();
        $this->adminGet($admin, '/api/admin/shipments', $token)->assertOk();
    }
}
