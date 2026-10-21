<?php

namespace Webkul\BagistoApi\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\User\Models\Admin;

abstract class AdminApiTestCase extends BagistoApiTestCase
{
    /**
     * GraphQL endpoint URL.
     */
    protected string $graphqlUrl = '/api/graphql';

    /**
     * Known plaintext password for admins created in tests.
     */
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
     * means it inherits the admin's current role permissions at runtime — every.
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

    /**
     * Keep only the keys that are real columns on the running core's schema.
     * Fixtures written against one core version carry columns another version dropped.
     */
    protected function existingColumns(string $table, array $row): array
    {
        return array_filter(
            $row,
            fn (string $column) => Schema::hasColumn($table, $column),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
