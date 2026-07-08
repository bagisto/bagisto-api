<?php

namespace Webkul\BagistoApi\Tests;

use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\User\Models\Admin;

/**
 * Base test case for the Admin API (REST + GraphQL).
 *
 * As of the 2026-05-27 auth refactor, admin endpoints authenticate via
 * pre-issued AdminPersonalAccessToken rows (table `admin_personal_access_tokens`)
 * resolved by the `admin-api` guard (AdminApiGuard). This base class issues
 * a fresh token per test admin via createToken() / actingAsAdminViaToken().
 *
 * This is distinct from Sanctum's `personal_access_tokens` table, which still
 * powers the storefront customer API.
 */
abstract class AdminApiTestCase extends BagistoApiTestCase
{
    /** GraphQL endpoint URL */
    protected string $graphqlUrl = '/api/graphql';

    /** Known plaintext password for admins created in tests. */
    protected string $adminPassword = 'admin123';

    /**
     * Create an admin with a known password.
     */
    protected function createAdmin(array $attributes = []): Admin
    {
        $this->seedRequiredData();

        return Admin::factory()->create(array_merge([
            'password' => bcrypt($this->adminPassword),
            'status' => 1,
        ], $attributes));
    }

    /**
     * Issue a fresh admin integration token (AdminPersonalAccessToken) for
     * an admin and return the plaintext Bearer token (`<id>|<random>`).
     *
     * Tokens issued by this helper:
     *   - status = active, permission_type = all (passes every role check)
     *   - expires_at = +1 day so they never auto-expire mid-test
     *   - no rate limits so parallel test runs don't hit throttles
     */
    protected function adminToken(Admin $admin): string
    {
        $plain = Str::random(40);

        $row = AdminPersonalAccessToken::create([
            'admin_id' => $admin->id,
            'name' => 'admin-api-test-'.Str::random(6),
            'token' => hash('sha256', $plain),
            'token_preview' => substr($plain, 0, 8),
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_ALL,
            'abilities' => [],
            'rate_limit_per_minute' => null,
            'rate_limit_per_day' => null,
            'expires_at' => now()->addDay(),
            'status' => AdminPersonalAccessToken::STATUS_ACTIVE,
            'created_by_admin_id' => $admin->id,
        ]);

        return $row->id.'|'.$plain;
    }

    /**
     * Issue a role-bound admin token. The token's `permission_type='same_as_web'`
     * means it inherits the admin's current role permissions at runtime — every
     * permission check on the API goes through the admin's role.
     *
     * Use this when the test is verifying that a specific role permission is
     * enforced (e.g. an admin without `sales.orders.cancel` should get 403).
     * Use the default `adminToken()` (permission_type=all) when the test is
     * verifying API behaviour itself and doesn't care about role gating.
     */
    protected function adminTokenSameAsWeb(Admin $admin): string
    {
        $plain = Str::random(40);

        $row = AdminPersonalAccessToken::create([
            'admin_id' => $admin->id,
            'name' => 'admin-api-test-saw-'.Str::random(6),
            'token' => hash('sha256', $plain),
            'token_preview' => substr($plain, 0, 8),
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_SAME_AS_WEB,
            'abilities' => [],
            'rate_limit_per_minute' => null,
            'rate_limit_per_day' => null,
            'expires_at' => now()->addDay(),
            'status' => AdminPersonalAccessToken::STATUS_ACTIVE,
            'created_by_admin_id' => $admin->id,
        ]);

        return $row->id.'|'.$plain;
    }

    /**
     * Convenience: create an admin (or use the supplied one) and bind their
     * Bearer token onto subsequent requests' default headers.
     */
    protected function actingAsAdminViaToken(?Admin $admin = null, ?string $token = null): Admin
    {
        $admin ??= $this->createAdmin();
        $token ??= $this->adminToken($admin);

        $this->withHeader('Authorization', 'Bearer '.$token);

        return $admin;
    }

    /**
     * Bearer auth headers for an admin.
     */
    protected function adminHeaders(Admin $admin, ?string $token = null): array
    {
        return [
            'Authorization' => 'Bearer '.($token ?? $this->adminToken($admin)),
        ];
    }

    /**
     * Authenticated admin REST GET.
     */
    protected function adminGet(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->getJson($url, $this->adminHeaders($admin, $token));
    }

    /**
     * Authenticated admin REST POST.
     */
    protected function adminPost(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->postJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Public (unauthenticated) REST POST.
     */
    protected function publicPost(string $url, array $data = []): TestResponse
    {
        return $this->postJson($url, $data);
    }

    /**
     * Public (unauthenticated) REST GET.
     */
    protected function publicGet(string $url): TestResponse
    {
        return $this->getJson($url);
    }

    /**
     * Execute a GraphQL request, optionally authenticated as an admin.
     */
    protected function adminGraphQL(string $query, array $variables = [], ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $payload = ['query' => $query];

        if (! empty($variables)) {
            $payload['variables'] = $variables;
        }

        $headers = $admin ? $this->adminHeaders($admin, $token) : [];

        return $this->postJson($this->graphqlUrl, $payload, $headers);
    }
}
