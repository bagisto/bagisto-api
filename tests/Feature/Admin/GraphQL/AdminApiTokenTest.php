<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class AdminApiTokenTest extends AdminApiTestCase
{
    /**
     * The profile query, used to prove a token works.
     */
    protected const PROFILE = 'query { readAdminProfile { id email tokenKind tokenId tokenExpiresAt } }';

    /**
     * The revoke mutation.
     */
    protected const REVOKE = 'mutation ($input: deleteAdminApiTokenInput!) { deleteAdminApiToken(input: $input) { adminApiToken { _id status revokedReason message } } }';

    // ========================================================================
    // Use
    // ========================================================================

    /**
     * Test an API token authenticates GraphQL requests and its use is recorded on the graphql channel.
     */
    public function test_an_api_token_authenticates_graphql_requests(): void
    {
        $admin = $this->createAdmin();
        $issued = $this->issueAdminApiToken($admin, 'localhost', now()->addDays(3));

        $profile = $this->adminEndpointGraphQL(self::PROFILE, [], $issued->plainText)->json('data.readAdminProfile');

        $this->assertSame($admin->email, $profile['email']);
        $this->assertSame('api_token', $profile['tokenKind']);
        $this->assertSame((int) $issued->token->id, $profile['tokenId']);
        $this->assertNotNull($profile['tokenExpiresAt']);

        $this->assertSame('graphql', $issued->token->fresh()->last_channel);
    }

    /**
     * Test an expired and a revoked API token are reported with their own codes.
     */
    public function test_expired_and_revoked_tokens_have_their_own_codes(): void
    {
        $admin = $this->createAdmin();
        $expiring = $this->adminApiToken($admin, 'localhost', now()->addDay());
        $revoked = $this->issueAdminApiToken($admin);

        $this->revoke($revoked->token->id, $this->adminAccessToken($admin));

        $response = $this->adminEndpointGraphQL(self::PROFILE, [], $revoked->plainText);

        $response->assertStatus(401);
        $this->assertSame('api_token_revoked', $response->json('errors.0.extensions.code'));

        $this->travel(2)->days();

        $this->assertSame('api_token_expired', $this->adminEndpointGraphQL(self::PROFILE, [], $expiring)->json('errors.0.extensions.code'));
    }

    /**
     * Test a token used from an address its domain does not resolve to is refused.
     */
    public function test_a_domain_mismatch_is_refused(): void
    {
        $this->domainResolver->set('erp.example.com', ['203.0.113.10']);

        $token = $this->adminApiToken($this->createAdmin(), 'erp.example.com');

        $response = $this->adminEndpointGraphQL(self::PROFILE, [], $token);

        $response->assertForbidden();
        $this->assertSame('api_token_domain_mismatch', $response->json('errors.0.extensions.code'));

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);

        $this->assertNull($this->adminEndpointGraphQL(self::PROFILE, [], $token)->json('errors'));
    }

    // ========================================================================
    // Management Operations
    // ========================================================================

    /**
     * Test adminApiTokens lists your own tokens without manage_others and every token with it.
     */
    public function test_the_list_is_scoped_by_manage_others(): void
    {
        $viewer = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens']);
        $manager = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens', 'settings.api_tokens.manage_others']);

        $own = $this->issueAdminApiToken($viewer)->token;
        $foreign = $this->issueAdminApiToken($this->createAdmin())->token;

        $query = 'query ($adminId: Int) { adminApiTokens(first: 50, admin_id: $adminId) { edges { node { _id name domain tokenPreview status } } } }';

        $viewerIds = collect($this->adminEndpointGraphQL($query, [], $this->adminAccessToken($viewer))->json('data.adminApiTokens.edges'))->pluck('node._id')->all();

        $this->assertContains((int) $own->id, $viewerIds);
        $this->assertNotContains((int) $foreign->id, $viewerIds);

        $managerNodes = collect($this->adminEndpointGraphQL($query, ['adminId' => (int) $foreign->admin_id], $this->adminAccessToken($manager))->json('data.adminApiTokens.edges'))->pluck('node');

        $this->assertSame([(int) $foreign->id], $managerNodes->pluck('_id')->all());
        $this->assertSame('active', $managerNodes->first()['status']);
    }

    /**
     * Test adminApiToken reads one token, and another admin's only with manage_others.
     */
    public function test_reading_a_token(): void
    {
        $viewer = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens']);
        $own = $this->issueAdminApiToken($viewer, 'erp.example.com');
        $foreign = $this->issueAdminApiToken($this->createAdmin());

        $query = fn (int $id) => "query { adminApiToken(id: \"/api/admin/settings/api-tokens/{$id}\") { _id domain tokenPreview resolvedIps domainMismatchCount } }";

        $token = $this->adminAccessToken($viewer);

        $read = $this->adminEndpointGraphQL($query((int) $own->token->id), [], $token)->json('data.adminApiToken');

        $this->assertSame((int) $own->token->id, $read['_id']);
        $this->assertSame('erp.example.com', $read['domain']);
        $this->assertSame($own->token->token_preview, $read['tokenPreview']);
        $this->assertSame(0, $read['domainMismatchCount']);

        $this->assertNotEmpty($this->adminEndpointGraphQL($query((int) $foreign->token->id), [], $token)->json('errors'));
    }

    /**
     * Test deleteAdminApiToken needs settings.api_tokens.delete and revokes at once.
     */
    public function test_revoking_a_token(): void
    {
        $viewer = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens']);
        $manager = $this->createAdminWithRolePermissions(['settings', 'settings.api_tokens', 'settings.api_tokens.delete']);

        $viewerToken = $this->issueAdminApiToken($viewer);
        $managerToken = $this->issueAdminApiToken($manager);

        $this->assertNotEmpty($this->revoke($viewerToken->token->id, $this->adminAccessToken($viewer))->json('errors'));
        $this->assertNull($viewerToken->token->fresh()->revoked_at);

        $revoked = $this->revoke($managerToken->token->id, $this->adminAccessToken($manager))->json('data.deleteAdminApiToken.adminApiToken');

        $this->assertSame('revoked', $revoked['status']);
        $this->assertSame('revoked_by_admin', $revoked['revokedReason']);
        $this->adminEndpointGraphQL(self::PROFILE, [], $managerToken->plainText)->assertStatus(401);

        $this->assertDatabaseHas('admin_api_audits', [
            'event' => AdminApiAuthAuditor::EVENT_API_TOKEN_REVOKED,
            'token_id' => $managerToken->token->id,
            'token_kind' => AdminApiAudit::TOKEN_KIND_API_TOKEN,
            'channel' => AdminApiAudit::CHANNEL_GRAPHQL,
        ]);
    }

    /**
     * Test the schema has no mutation that creates an API token.
     */
    public function test_api_tokens_cannot_be_created_over_graphql(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminEndpointGraphQL(
            'mutation { createAdminApiToken(input: { name: "Minted" }) { adminApiToken { _id } } }',
            [],
            $this->adminApiToken($admin),
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($response->json('data.createAdminApiToken'));
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
     * Send deleteAdminApiToken with a bearer token.
     */
    protected function revoke(int $id, string $bearer): TestResponse
    {
        return $this->adminEndpointGraphQL(self::REVOKE, [
            'input' => ['id' => '/api/admin/settings/api-tokens/'.$id],
        ], $bearer);
    }
}
