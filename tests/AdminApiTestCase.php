<?php

namespace Webkul\BagistoApi\Tests;

use Illuminate\Testing\TestResponse;
use Webkul\User\Models\Admin;

/**
 * Base test case for the Admin API (REST + GraphQL).
 *
 * Reuses BagistoApiTestCase, which disables the storefront/admin-key
 * middleware, then adds admin-token authentication helpers. Admin API tokens
 * are Sanctum personal access tokens issued on the Admin model.
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
            'status'   => 1,
        ], $attributes));
    }

    /**
     * Issue a Sanctum API token for an admin.
     */
    protected function adminToken(Admin $admin): string
    {
        return $admin->createToken('admin-api-test')->plainTextToken;
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
