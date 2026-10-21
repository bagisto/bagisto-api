<?php

namespace Webkul\BagistoApi\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenService;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtKeyStore;
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
     * Start a real login session for an admin and return its JWT access token.
     */
    protected function adminToken(Admin $admin): string
    {
        $this->ensureJwtKeys();

        return app(AdminApiTokenService::class)
            ->issue($admin, [
                'name' => 'admin-api-test-'.Str::random(6),
                'client_type' => 'other',
            ])
            ->accessToken;
    }

    /**
     * Issue an access token for an admin; permissions always follow the admin's role now.
     */
    protected function adminTokenSameAsWeb(Admin $admin): string
    {
        return $this->adminToken($admin);
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
     * Point the JWT key store at a per-process temporary directory and create a key pair there once.
     */
    protected function ensureJwtKeys(): void
    {
        config(['bagistoapi.admin_auth.jwt.path' => sys_get_temp_dir().'/bagisto-api-test-jwt-'.(getenv('TEST_TOKEN') ?: '0')]);

        $keys = app(JwtKeyStore::class);

        if (! $keys->exists()) {
            $keys->create();
        }
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
