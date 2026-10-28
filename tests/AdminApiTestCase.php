<?php

namespace Webkul\BagistoApi\Tests;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenPair;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenService;
use Webkul\BagistoApi\Admin\Services\DomainResolver;
use Webkul\BagistoApi\Admin\Services\IssuedAdminApiToken;
use Webkul\BagistoApi\Tests\Concerns\CraftsAdminJwts;
use Webkul\BagistoApi\Tests\Fakes\FakeDomainResolver;
use Webkul\Core\Models\CoreConfig;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

abstract class AdminApiTestCase extends BagistoApiTestCase
{
    use CraftsAdminJwts;

    /**
     * GraphQL endpoint URL.
     */
    protected string $graphqlUrl = '/api/graphql';

    /**
     * The admin GraphQL endpoint, where the public auth mutations are allowlisted.
     */
    protected string $adminGraphqlUrl = '/api/admin/graphql';

    /**
     * Known plaintext password for admins created in tests.
     */
    protected string $adminPassword = 'admin123';

    /**
     * The DNS resolver the domain binding uses in tests.
     */
    protected FakeDomainResolver $domainResolver;

    /**
     * Sign with the test JWT key pair and resolve domains through a fake resolver.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureJwtKeys();

        $this->domainResolver = new FakeDomainResolver([
            'localhost' => ['127.0.0.1', '::1'],
        ]);

        $this->app->instance(DomainResolver::class, $this->domainResolver);
    }

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
     * Create an admin whose custom role grants exactly the given permissions.
     */
    protected function createAdminWithRolePermissions(array $permissions, array $attributes = []): Admin
    {
        $role = Role::create([
            'name' => 'API test role '.Str::random(8),
            'description' => 'Created by an admin API test.',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(array_merge(['role_id' => $role->id], $attributes));
    }

    /**
     * Start a real login session for an admin and return its access and refresh tokens.
     */
    protected function adminTokenPair(Admin $admin, array $client = []): AdminApiTokenPair
    {
        return app(AdminApiTokenService::class)->issue($admin, array_merge([
            'name' => 'admin-api-test-'.Str::random(6),
            'client_type' => AdminApiSession::CLIENT_TYPE_OTHER,
        ], $client));
    }

    /**
     * Start a real login session for an admin and return its JWT access token.
     */
    protected function adminAccessToken(Admin $admin, array $client = []): string
    {
        return $this->adminTokenPair($admin, $client)->accessToken;
    }

    /**
     * Start a real login session for an admin and return its refresh token.
     */
    protected function adminRefreshToken(Admin $admin, array $client = []): string
    {
        return $this->adminTokenPair($admin, $client)->refreshToken;
    }

    /**
     * Create an API token for an admin, bound to a domain, and return the stored row with its string.
     */
    protected function issueAdminApiToken(Admin $admin, string $domain = 'localhost', ?CarbonInterface $expiresAt = null, string $name = 'Test integration'): IssuedAdminApiToken
    {
        return app(AdminApiTokenIssuer::class)->create(null, $admin, $name, $domain, $expiresAt, Request::create('/'));
    }

    /**
     * Create an API token for an admin, bound to a domain, and return its JWT string.
     */
    protected function adminApiToken(Admin $admin, string $domain = 'localhost', ?CarbonInterface $expiresAt = null): string
    {
        return $this->issueAdminApiToken($admin, $domain, $expiresAt)->plainText;
    }

    /**
     * Move the clock past the configured access token lifetime and the verification leeway.
     */
    protected function expireAccessToken(): void
    {
        $seconds = app(AdminApiAuthSettings::class)->accessTokenTtl() * 60
            + (int) config('bagistoapi.admin_auth.jwt.leeway', 30)
            + 1;

        $this->travel($seconds)->seconds();
    }

    /**
     * Store an admin authentication setting, such as "security.domain_binding_mode".
     */
    protected function setAdminAuthConfig(string $field, mixed $value): void
    {
        $code = AdminApiAuthSettings::CONFIG_PREFIX.'.'.$field;

        CoreConfig::query()->where('code', $code)->delete();

        CoreConfig::query()->create([
            'code' => $code,
            'value' => is_bool($value) ? (string) (int) $value : (string) $value,
        ]);

        $this->forgetCoreConfigCache();
    }

    /**
     * Convenience: create an admin (or use the supplied one) and bind their
     * Bearer token onto subsequent requests' default headers.
     */
    protected function actingAsAdminViaToken(?Admin $admin = null, ?string $token = null): Admin
    {
        $admin ??= $this->createAdmin();
        $token ??= $this->adminAccessToken($admin);

        $this->withHeader('Authorization', 'Bearer '.$token);

        return $admin;
    }

    /**
     * Bearer auth headers for an admin.
     */
    protected function adminHeaders(Admin $admin, ?string $token = null): array
    {
        return [
            'Authorization' => 'Bearer '.($token ?? $this->adminAccessToken($admin)),
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
     * Execute a request on the admin GraphQL endpoint with an optional raw Bearer token.
     */
    protected function adminEndpointGraphQL(string $query, array $variables = [], ?string $token = null, array $headers = []): TestResponse
    {
        $payload = ['query' => $query];

        if (! empty($variables)) {
            $payload['variables'] = $variables;
        }

        if ($token !== null) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $this->postJson($this->adminGraphqlUrl, $payload, $headers);
    }

    /**
     * Send a REST request with a raw Bearer token.
     */
    protected function withBearer(string $method, string $url, string $token, array $data = [], array $headers = []): TestResponse
    {
        return $this->json($method, $url, $data, array_merge($headers, [
            'Authorization' => 'Bearer '.$token,
        ]));
    }

    /**
     * Revoke every login session of an admin, so their access tokens stop working.
     */
    protected function revokeAdminSessions(Admin $admin): void
    {
        AdminApiSession::query()
            ->where('admin_id', $admin->id)
            ->update([
                'revoked_at' => now(),
                'revoked_reason' => 'test',
            ]);
    }

    /**
     * Move every login session of an admin past its absolute lifetime.
     */
    protected function expireAdminSessions(Admin $admin): void
    {
        AdminApiSession::query()
            ->where('admin_id', $admin->id)
            ->update(['expires_at' => now()->subDay()]);
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
