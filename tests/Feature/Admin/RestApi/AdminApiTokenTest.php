<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;

class AdminApiTokenTest extends AdminApiTestCase
{
    /**
     * The REST profile path, used to prove a token works.
     */
    protected const PROFILE = '/api/admin/get';

    /**
     * The REST API token collection path.
     */
    protected const TOKENS = '/api/admin/settings/api-tokens';

    // ========================================================================
    // Use
    // ========================================================================

    /**
     * Test an API token authenticates REST requests as its admin.
     */
    public function test_an_api_token_authenticates_rest_requests(): void
    {
        $admin = $this->createAdmin();
        $issued = $this->issueAdminApiToken($admin);

        $response = $this->withBearer('GET', self::PROFILE, $issued->plainText)->assertOk();

        $body = $response->json();
        $row = isset($body[0]) ? $body[0] : $body;

        $this->assertSame($admin->email, $row['email']);
        $this->assertSame('api_token', $row['tokenKind']);
        $this->assertSame((int) $issued->token->id, $row['tokenId']);
    }

    /**
     * Test the API token JWT names its domain as audience and carries no session.
     */
    public function test_the_api_token_claims(): void
    {
        $admin = $this->createAdmin();
        $issued = $this->issueAdminApiToken($admin, 'https://ERP.Example.com/sync', now()->addDays(10));

        $claims = $this->jwtClaims($issued->plainText);

        $this->assertSame('api', $claims['typ']);
        $this->assertSame('erp.example.com', $claims['aud']);
        $this->assertSame($issued->token->jti, $claims['jti']);
        $this->assertSame((int) $admin->id, $claims['sub']);
        $this->assertArrayNotHasKey('sid', $claims);
        $this->assertSame($issued->token->expires_at->getTimestamp(), $claims['exp']);
        $this->assertSame(substr($issued->plainText, -8), $issued->token->token_preview);
    }

    /**
     * Test the usage of an API token is recorded on its row.
     */
    public function test_usage_is_recorded_on_the_token(): void
    {
        $issued = $this->issueAdminApiToken($this->createAdmin());

        $this->withBearer('GET', self::PROFILE, $issued->plainText, [], ['User-Agent' => 'erp-sync/1.0'])->assertOk();

        $token = $issued->token->fresh();

        $this->assertSame('127.0.0.1', $token->last_ip_address);
        $this->assertSame('rest', $token->last_channel);
        $this->assertSame('erp-sync/1.0', $token->last_user_agent);
        $this->assertNotNull($token->last_used_at);
    }

    // ========================================================================
    // Expiry And Revocation
    // ========================================================================

    /**
     * Test a token without expiry has no exp claim and keeps working.
     */
    public function test_a_token_without_expiry_keeps_working(): void
    {
        $token = $this->adminApiToken($this->createAdmin());

        $this->assertArrayNotHasKey('exp', $this->jwtClaims($token));

        $this->travel(400)->days();

        $this->withBearer('GET', self::PROFILE, $token)->assertOk();
    }

    /**
     * Test an expired API token is reported as expired.
     */
    public function test_an_expired_api_token_is_reported_as_expired(): void
    {
        $token = $this->adminApiToken($this->createAdmin(), 'localhost', now()->addDay());

        $this->travel(2)->days();

        $this->withBearer('GET', self::PROFILE, $token)
            ->assertStatus(401)
            ->assertJsonPath('error', 'api_token_expired');
    }

    /**
     * Test a revoked API token is reported as revoked, and revoking it twice is a conflict.
     */
    public function test_a_revoked_api_token_is_reported_as_revoked(): void
    {
        $admin = $this->createAdmin();
        $issued = $this->issueAdminApiToken($admin);

        $this->adminDeleteToken($admin, $issued->token->id)->assertNoContent();

        $this->withBearer('GET', self::PROFILE, $issued->plainText)
            ->assertStatus(401)
            ->assertJsonPath('error', 'api_token_revoked');

        $this->adminDeleteToken($admin, $issued->token->id)->assertStatus(409);

        $this->assertSame(AdminApiToken::REVOKED_BY_ADMIN, $issued->token->fresh()->revoked_reason);
    }

    /**
     * Test a token whose claims no longer match its stored row is rejected.
     */
    public function test_claims_must_match_the_stored_token(): void
    {
        $admin = $this->createAdmin();
        $claims = $this->jwtClaims($this->adminApiToken($admin));

        foreach ([
            'another domain' => array_merge($claims, ['aud' => 'other.example.com']),
            'another admin' => array_merge($claims, ['sub' => (int) $this->createAdmin()->id]),
            'an unknown jti' => array_merge($claims, ['jti' => bin2hex(random_bytes(16))]),
            'a session id' => array_merge($claims, ['sid' => 1]),
            'the access audience' => array_merge($claims, ['aud' => 'bagisto-admin-api']),
        ] as $case => $forged) {
            $response = $this->withBearer('GET', self::PROFILE, $this->signJwt($forged));

            $this->assertSame(401, $response->getStatusCode(), $case.' was accepted');
        }

        $this->withBearer('GET', self::PROFILE, $this->signJwt($claims))->assertOk();
    }

    // ========================================================================
    // Domain Binding
    // ========================================================================

    /**
     * Test in dns mode a request from an address the domain resolves to passes and any other is refused.
     */
    public function test_dns_binding_matches_the_client_address(): void
    {
        $this->domainResolver->set('erp.example.com', ['203.0.113.10']);

        $issued = $this->issueAdminApiToken($this->createAdmin(), 'erp.example.com');

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);
        $this->withBearer('GET', self::PROFILE, $issued->plainText)->assertOk();

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.5']);
        $this->withBearer('GET', self::PROFILE, $issued->plainText)
            ->assertForbidden()
            ->assertJsonPath('error', 'api_token_domain_mismatch');

        $token = $issued->token->fresh();

        $this->assertSame(1, (int) $token->domain_mismatch_count);
        $this->assertNotNull($token->last_domain_mismatch_at);
        $this->assertSame(['203.0.113.10'], $token->resolved_ips);
    }

    /**
     * Test dns binding compares IPv6 addresses by value, not by spelling.
     */
    public function test_dns_binding_matches_ipv6(): void
    {
        $this->domainResolver->set('v6.example.com', ['2001:db8::1']);

        $token = $this->adminApiToken($this->createAdmin(), 'v6.example.com');

        $this->withServerVariables(['REMOTE_ADDR' => '2001:0db8:0000:0000:0000:0000:0000:0001']);
        $this->withBearer('GET', self::PROFILE, $token)->assertOk();

        $this->withServerVariables(['REMOTE_ADDR' => '2001:db8::2']);
        $this->withBearer('GET', self::PROFILE, $token)->assertForbidden();
    }

    /**
     * Test resolved addresses are cached for five minutes and then looked up again.
     */
    public function test_resolved_addresses_are_cached_for_five_minutes(): void
    {
        $this->domainResolver->set('erp.example.com', ['203.0.113.10']);

        $token = $this->adminApiToken($this->createAdmin(), 'erp.example.com');

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);
        $this->withBearer('GET', self::PROFILE, $token)->assertOk();

        $this->domainResolver->set('erp.example.com', ['203.0.113.20']);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20']);
        $this->withBearer('GET', self::PROFILE, $token)->assertForbidden();

        $this->assertSame(1, $this->domainResolver->lookupsOf('erp.example.com'));

        $this->travel(301)->seconds();

        $this->withBearer('GET', self::PROFILE, $token)->assertOk();
        $this->assertSame(2, $this->domainResolver->lookupsOf('erp.example.com'));
    }

    /**
     * Test in origin mode the Origin host, or the Referer host without one, must equal the domain.
     */
    public function test_origin_binding_matches_the_origin_or_referer_host(): void
    {
        $this->setAdminAuthConfig('security.domain_binding_mode', 'origin');

        $token = $this->adminApiToken($this->createAdmin(), 'erp.example.com');

        $this->withBearer('GET', self::PROFILE, $token, [], ['Origin' => 'https://erp.example.com'])->assertOk();
        $this->withBearer('GET', self::PROFILE, $token, [], ['Referer' => 'https://erp.example.com/orders?page=2'])->assertOk();

        $this->withBearer('GET', self::PROFILE, $token, [], ['Origin' => 'https://evil.example.com'])
            ->assertForbidden()
            ->assertJsonPath('error', 'api_token_domain_mismatch');

        $this->withBearer('GET', self::PROFILE, $token)->assertForbidden();
        $this->withBearer('GET', self::PROFILE, $token, [], ['Origin' => 'null'])->assertForbidden();
    }

    /**
     * Test the domain binding is checked before any permission.
     */
    public function test_the_domain_is_checked_before_the_permission(): void
    {
        $admin = $this->createAdminWithRolePermissions(['dashboard']);
        $token = $this->adminApiToken($admin, 'erp.example.com');

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.5']);

        $this->withBearer('GET', '/api/admin/settings/users', $token)
            ->assertForbidden()
            ->assertJsonPath('error', 'api_token_domain_mismatch');
    }

    // ========================================================================
    // Admin State And Roles
    // ========================================================================

    /**
     * Test an inactive admin's API token is refused, and accepted again after reactivation.
     */
    public function test_an_inactive_admins_token_works_again_after_reactivation(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminApiToken($admin);

        $admin->update(['status' => 0]);

        $this->withBearer('GET', self::PROFILE, $token)->assertStatus(401);

        $admin->update(['status' => 1]);

        $this->withBearer('GET', self::PROFILE, $token)->assertOk();
    }

    /**
     * Test a role change applies to an API token on its next request.
     */
    public function test_a_role_change_applies_on_the_next_request(): void
    {
        $admin = $this->createAdminWithRolePermissions(['settings', 'settings.users']);
        $token = $this->adminApiToken($admin);

        $this->withBearer('GET', '/api/admin/settings/users', $token)->assertOk();

        $admin->role->update(['permissions' => ['dashboard']]);

        $this->withBearer('GET', '/api/admin/settings/users', $token)->assertForbidden();
    }

    /**
     * Test API tokens survive a password change unless the setting says otherwise.
     */
    public function test_api_tokens_survive_a_password_change_unless_configured(): void
    {
        $admin = $this->createAdmin();
        $kept = $this->adminApiToken($admin);

        $admin->update(['password' => bcrypt('changed-secret-1')]);

        $this->withBearer('GET', self::PROFILE, $kept)->assertOk();

        $this->setAdminAuthConfig('security.revoke_api_tokens_on_password_change', true);

        $admin->fresh()->update(['password' => bcrypt('changed-secret-2')]);

        $this->withBearer('GET', self::PROFILE, $kept)
            ->assertStatus(401)
            ->assertJsonPath('error', 'api_token_revoked');
    }

    // ========================================================================
    // Management Endpoints
    // ========================================================================

    /**
     * Test API tokens cannot be created over the API, with an API token or an access token.
     */
    public function test_api_tokens_cannot_be_created_over_the_api(): void
    {
        $admin = $this->createAdmin();
        $apiToken = $this->adminApiToken($admin);
        $before = AdminApiToken::query()->count();

        $payload = ['name' => 'Minted', 'domain' => 'erp.example.com'];

        $this->assertNotContains($this->withBearer('POST', self::TOKENS, $apiToken, $payload)->getStatusCode(), [200, 201]);
        $this->assertNotContains($this->adminPost($admin, self::TOKENS, $payload)->getStatusCode(), [200, 201]);

        $this->assertSame($before, AdminApiToken::query()->count());
    }

    /**
     * Test the list needs settings.api_tokens and shows only your own tokens without manage_others.
     */
    public function test_the_list_is_scoped_by_manage_others(): void
    {
        $viewer = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens']);
        $manager = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens', 'settings.api_tokens.manage_others']);
        $outsider = $this->createAdminWithRolePermissions(['settings']);

        $own = $this->issueAdminApiToken($viewer)->token;
        $foreign = $this->issueAdminApiToken($this->createAdmin())->token;

        $this->adminGet($outsider, self::TOKENS)->assertForbidden();

        $viewerIds = collect($this->adminGet($viewer, self::TOKENS.'?per_page=50')->assertOk()->json('data'))->pluck('id')->all();

        $this->assertContains((int) $own->id, $viewerIds);
        $this->assertNotContains((int) $foreign->id, $viewerIds);

        $managerIds = collect($this->adminGet($manager, self::TOKENS.'?per_page=50&admin_id='.$foreign->admin_id)->assertOk()->json('data'))->pluck('id')->all();

        $this->assertSame([(int) $foreign->id], $managerIds);
    }

    /**
     * Test the list filters by status and domain and never returns the token string.
     */
    public function test_the_list_filters_and_hides_the_token(): void
    {
        $admin = $this->createAdmin();
        $active = $this->issueAdminApiToken($admin, 'erp.example.com');
        $revoked = $this->issueAdminApiToken($admin, 'pim.example.com');

        $this->adminDeleteToken($admin, $revoked->token->id)->assertNoContent();

        $byStatus = collect($this->adminGet($admin, self::TOKENS.'?per_page=50&status=revoked&admin_id='.$admin->id)->assertOk()->json('data'));
        $byDomain = collect($this->adminGet($admin, self::TOKENS.'?per_page=50&domain=erp.example.com&admin_id='.$admin->id)->assertOk()->json('data'));

        $this->assertSame([(int) $revoked->token->id], $byStatus->pluck('id')->all());
        $this->assertSame([(int) $active->token->id], $byDomain->pluck('id')->all());
        $this->assertSame('erp.example.com', $byDomain->first()['domain']);
        $this->assertStringNotContainsString($active->plainText, json_encode($byDomain->all()));
    }

    /**
     * Test another admin's token is not found without manage_others.
     */
    public function test_reading_another_admins_token_needs_manage_others(): void
    {
        $viewer = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens', 'settings.api_tokens.delete']);
        $manager = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens', 'settings.api_tokens.delete', 'settings.api_tokens.manage_others']);

        $foreign = $this->issueAdminApiToken($this->createAdmin());

        $this->adminGet($viewer, self::TOKENS.'/'.$foreign->token->id)->assertNotFound();
        $this->adminDeleteToken($viewer, $foreign->token->id)->assertNotFound();
        $this->withBearer('GET', self::PROFILE, $foreign->plainText)->assertOk();

        $this->adminGet($manager, self::TOKENS.'/'.$foreign->token->id)
            ->assertOk()
            ->assertJsonPath('id', (int) $foreign->token->id)
            ->assertJsonPath('status', 'active');

        $this->adminDeleteToken($manager, $foreign->token->id)->assertNoContent();
        $this->withBearer('GET', self::PROFILE, $foreign->plainText)->assertStatus(401);
    }

    /**
     * Test revoking needs settings.api_tokens.delete even for your own token.
     */
    public function test_revoking_needs_the_delete_permission(): void
    {
        $viewer = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens']);
        $issued = $this->issueAdminApiToken($viewer);

        $this->adminDeleteToken($viewer, $issued->token->id)->assertForbidden();

        $this->assertNull($issued->token->fresh()->revoked_at);
    }

    /**
     * Test an API token can revoke itself when its role allows it.
     */
    public function test_an_api_token_can_revoke_itself(): void
    {
        $issued = $this->issueAdminApiToken($this->createAdmin());

        $this->withBearer('DELETE', self::TOKENS.'/'.$issued->token->id, $issued->plainText)->assertNoContent();

        $this->withBearer('GET', self::PROFILE, $issued->plainText)->assertJsonPath('error', 'api_token_revoked');
    }

    // ========================================================================
    // Audit
    // ========================================================================

    /**
     * Test creating, revoking and a domain mismatch are audited against the API token.
     */
    public function test_create_revoke_and_mismatch_are_audited(): void
    {
        $this->domainResolver->set('erp.example.com', ['203.0.113.10']);

        $admin = $this->createAdmin();
        $issued = $this->issueAdminApiToken($admin, 'erp.example.com');

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.5']);
        $this->withBearer('GET', self::PROFILE, $issued->plainText)->assertForbidden();

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);
        $this->adminDeleteToken($admin, $issued->token->id)->assertNoContent();

        $this->assertDatabaseHas('admin_api_audits', [
            'event' => AdminApiAuthAuditor::EVENT_API_TOKEN_CREATED,
            'token_kind' => AdminApiAudit::TOKEN_KIND_API_TOKEN,
            'token_id' => $issued->token->id,
            'channel' => AdminApiAudit::CHANNEL_CLI,
        ]);

        $this->assertDatabaseHas('admin_api_audits', [
            'event' => AdminApiAuthAuditor::EVENT_DOMAIN_MISMATCH,
            'token_kind' => AdminApiAudit::TOKEN_KIND_API_TOKEN,
            'token_id' => $issued->token->id,
            'ip_address' => '198.51.100.5',
            'channel' => AdminApiAudit::CHANNEL_REST,
        ]);

        $this->assertDatabaseHas('admin_api_audits', [
            'event' => AdminApiAuthAuditor::EVENT_API_TOKEN_REVOKED,
            'user_id' => $admin->id,
            'token_kind' => AdminApiAudit::TOKEN_KIND_API_TOKEN,
            'token_id' => $issued->token->id,
            'channel' => AdminApiAudit::CHANNEL_REST,
        ]);
    }

    /**
     * Start every test with an empty audit trail.
     */
    protected function setUp(): void
    {
        parent::setUp();

        AdminApiAudit::query()->delete();
    }

    /**
     * Send an authenticated DELETE for an API token as an admin's login session.
     */
    protected function adminDeleteToken(Admin $admin, int $id): TestResponse
    {
        return $this->deleteJson(self::TOKENS.'/'.$id, [], $this->adminHeaders($admin));
    }
}
