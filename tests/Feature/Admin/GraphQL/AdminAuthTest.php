<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PragmaRX\Google2FA\Google2FA;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;

class AdminAuthTest extends AdminApiTestCase
{
    /**
     * The fields every token pair answer is read with.
     */
    protected const PAIR_FIELDS = 'tokenType accessToken expiresIn accessTokenExpiresAt refreshToken refreshTokenExpiresAt session admin permissions';

    /**
     * The profile query, used to prove a token works.
     */
    protected const PROFILE = 'query { readAdminProfile { id email tokenKind tokenId } }';

    // ========================================================================
    // Login
    // ========================================================================

    /**
     * Test createAdminLogin is reachable without a token and returns a token pair.
     */
    public function test_login_returns_a_token_pair_without_a_token(): void
    {
        $admin = $this->createAdmin();

        $response = $this->login($admin->email, $this->adminPassword, 'Tablet', 'pos');

        $response->assertOk();

        $login = $response->json('data.createAdminLogin.adminLogin');

        $this->assertIsArray($login, 'login failed: '.$response->getContent());
        $this->assertSame('Bearer', $login['tokenType']);
        $this->assertSame(900, $login['expiresIn']);
        $this->assertFalse($login['twoFactorRequired']);
        $this->assertSame('Tablet', $login['session']['name']);
        $this->assertSame('pos', $login['session']['clientType']);
        $this->assertSame((int) $admin->id, $login['admin']['id']);
        $this->assertMatchesRegularExpression('/^\d+\|[0-9a-f]{64}$/', $login['refreshToken']);

        $profile = $this->adminEndpointGraphQL(self::PROFILE, [], $login['accessToken'])->json('data.readAdminProfile');

        $this->assertSame($admin->email, $profile['email']);
        $this->assertSame('session', $profile['tokenKind']);
        $this->assertSame($login['session']['id'], $profile['tokenId']);
    }

    /**
     * Test a token from GraphQL login works on REST, and one from REST login works on GraphQL.
     */
    public function test_tokens_work_across_transports(): void
    {
        $admin = $this->createAdmin();

        $graphQlToken = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin.accessToken');
        $restToken = $this->postJson('/api/admin/auth/login', [
            'email' => $admin->email,
            'password' => $this->adminPassword,
        ])->json('accessToken');

        $this->withBearer('GET', '/api/admin/get', $graphQlToken)->assertOk();
        $this->assertSame($admin->email, $this->adminEndpointGraphQL(self::PROFILE, [], $restToken)->json('data.readAdminProfile.email'));
    }

    /**
     * Test the client type is stored, and a missing one is stored as other.
     */
    public function test_client_type_is_stored_on_the_session(): void
    {
        $admin = $this->createAdmin();

        $pos = $this->login($admin->email, $this->adminPassword, null, 'pos')->json('data.createAdminLogin.adminLogin.session.id');
        $none = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin.session.id');

        $this->assertSame('pos', AdminApiSession::query()->find($pos)->client_type);
        $this->assertSame('other', AdminApiSession::query()->find($none)->client_type);
    }

    /**
     * Test a wrong password and an unknown email get the same error code and message.
     */
    public function test_wrong_password_and_unknown_email_get_the_same_answer(): void
    {
        $admin = $this->createAdmin();

        $wrongPassword = $this->login($admin->email, 'not-the-password');
        $unknownEmail = $this->login('nobody-'.uniqid().'@example.com', 'not-the-password');

        $this->assertSame('invalid_credentials', $wrongPassword->json('errors.0.extensions.code'));
        $this->assertSame('invalid_credentials', $unknownEmail->json('errors.0.extensions.code'));
        $this->assertSame($wrongPassword->json('errors.0.message'), $unknownEmail->json('errors.0.message'));
        $this->assertNull($wrongPassword->json('data.createAdminLogin'));
    }

    /**
     * Test an inactive admin cannot log in.
     */
    public function test_an_inactive_admin_cannot_log_in(): void
    {
        $admin = $this->createAdmin(['status' => 0]);

        $this->assertSame('account_inactive', $this->login($admin->email, $this->adminPassword)->json('errors.0.extensions.code'));
    }

    /**
     * Test login validates its input.
     */
    public function test_login_validates_its_input(): void
    {
        $this->assertNotEmpty($this->login('not-an-email', 'secret')->json('errors'));
        $this->assertNotEmpty($this->login('admin@example.com', 'secret', null, 'fridge')->json('errors'));
    }

    /**
     * Test the sixth failed login in a minute is locked out with a retry delay.
     */
    public function test_the_sixth_failed_login_is_locked_out(): void
    {
        $admin = $this->createAdmin();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->login($admin->email, 'wrong-'.$attempt);
        }

        $response = $this->login($admin->email, $this->adminPassword);

        $this->assertSame('too_many_login_attempts', $response->json('errors.0.extensions.code'));
        $this->assertGreaterThan(0, (int) $response->json('errors.0.extensions.retryAfter'));
    }

    // ========================================================================
    // Public Allowlist
    // ========================================================================

    /**
     * Test a document mixing a public mutation with a protected field is refused without a token.
     */
    public function test_a_mixed_document_is_not_public(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminEndpointGraphQL(<<<GQL
            mutation {
              createAdminLogin(input: { email: "{$admin->email}", password: "{$this->adminPassword}" }) { adminLogin { accessToken } }
              createAdminLogoutAll(input: {}) { adminLogoutAll { revoked } }
            }
        GQL);

        $response->assertStatus(401);
        $this->assertSame('unauthenticated', $response->json('errors.0.extensions.code'));
        $this->assertSame(0, AdminApiSession::query()->where('admin_id', $admin->id)->count());
    }

    /**
     * Test a query is never public, even one named like a public mutation.
     */
    public function test_queries_are_not_public(): void
    {
        $this->adminEndpointGraphQL('query createAdminLogin { readAdminProfile { id } }')->assertStatus(401);
        $this->adminEndpointGraphQL('{ __schema { types { name } } }')->assertStatus(401);
    }

    /**
     * Test a public mutation in the query string cannot make the body's document public.
     */
    public function test_a_query_string_document_does_not_make_the_body_public(): void
    {
        $admin = $this->createAdmin();

        $login = 'mutation { createAdminLogin(input: { email: "'.$admin->email.'", password: "x" }) { adminLogin { accessToken } } }';

        $json = $this->postJson($this->adminGraphqlUrl.'?query='.rawurlencode($login), ['query' => self::PROFILE]);

        $json->assertStatus(401);
        $this->assertSame('unauthenticated', $json->json('errors.0.extensions.code'));

        $plain = $this->call(
            'POST',
            $this->adminGraphqlUrl.'?query='.rawurlencode($login),
            server: ['CONTENT_TYPE' => 'text/plain', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode(['query' => self::PROFILE]),
        );

        $plain->assertStatus(401);
        $this->assertNull($plain->json('data.readAdminProfile'));
    }

    /**
     * Test a batch of documents is never public.
     */
    public function test_a_batched_body_is_not_public(): void
    {
        $admin = $this->createAdmin();

        $this->postJson($this->adminGraphqlUrl, [[
            'query' => 'mutation { createAdminLogin(input: { email: "'.$admin->email.'", password: "x" }) { adminLogin { accessToken } } }',
        ]])->assertStatus(401);
    }

    // ========================================================================
    // Two-Factor Authentication
    // ========================================================================

    /**
     * Test two-factor login returns a challenge that a valid code exchanges for a pair.
     */
    public function test_two_factor_login_with_a_valid_code(): void
    {
        $admin = $this->createAdmin();
        $secret = $this->enableTwoFactor($admin);

        $challenge = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin');

        $this->assertTrue($challenge['twoFactorRequired']);
        $this->assertNull($challenge['accessToken']);
        $this->assertNotEmpty($challenge['challengeToken']);

        $verified = $this->verifyTwoFactor($challenge['challengeToken'], (new Google2FA)->getCurrentOtp($secret))
            ->json('data.createAdminTwoFactorVerify.adminTwoFactorVerify');

        $this->assertNotEmpty($verified['accessToken']);
        $this->assertSame($admin->email, $this->adminEndpointGraphQL(self::PROFILE, [], $verified['accessToken'])->json('data.readAdminProfile.email'));
    }

    /**
     * Test a wrong code is refused and a used challenge cannot be used again.
     */
    public function test_two_factor_rejects_a_wrong_code_and_a_used_challenge(): void
    {
        $admin = $this->createAdmin();
        $secret = $this->enableTwoFactor($admin);

        $challenge = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin.challengeToken');

        $current = (new Google2FA)->getCurrentOtp($secret);
        $wrong = str_pad((string) (((int) $current + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $this->assertSame('invalid_two_factor_code', $this->verifyTwoFactor($challenge, $wrong)->json('errors.0.extensions.code'));

        $this->verifyTwoFactor($challenge, $current)->assertJsonPath('errors', null);

        $this->assertSame('two_factor_challenge_invalid', $this->verifyTwoFactor($challenge, $current)->json('errors.0.extensions.code'));
    }

    /**
     * Test five wrong codes revoke the challenge, so even the right code is then refused.
     */
    public function test_five_wrong_codes_revoke_the_challenge(): void
    {
        $admin = $this->createAdmin();
        $secret = $this->enableTwoFactor($admin);

        $challenge = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin.challengeToken');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->verifyTwoFactor($challenge, $this->wrongOtp($secret));
        }

        $this->travel(61)->seconds();

        $this->assertSame(
            'two_factor_challenge_invalid',
            $this->verifyTwoFactor($challenge, (new Google2FA)->getCurrentOtp($secret))->json('errors.0.extensions.code'),
        );
    }

    /**
     * Test a backup code finishes the login once and is burned.
     */
    public function test_a_backup_code_works_once(): void
    {
        $admin = $this->createAdmin();
        $this->enableTwoFactor($admin, ['670089', '569097']);

        $first = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin.challengeToken');

        $verified = $this->verifyTwoFactor($first, backupCode: '670089');

        $this->assertNotEmpty($verified->json('data.createAdminTwoFactorVerify.adminTwoFactorVerify.accessToken'), $verified->getContent());
        $this->assertCount(1, $admin->fresh()->two_factor_backup_codes);

        $second = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin.challengeToken');

        $this->assertSame('invalid_two_factor_code', $this->verifyTwoFactor($second, backupCode: '670089')->json('errors.0.extensions.code'));
    }

    /**
     * Test a challenge expires after five minutes.
     */
    public function test_a_challenge_expires_after_five_minutes(): void
    {
        $admin = $this->createAdmin();
        $this->enableTwoFactor($admin);

        $challenge = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin.challengeToken');

        $this->travel(6)->minutes();

        $this->assertSame('two_factor_challenge_invalid', $this->verifyTwoFactor($challenge, backupCode: '670089')->json('errors.0.extensions.code'));
    }

    // ========================================================================
    // Access Token
    // ========================================================================

    /**
     * Test an expired access token is reported as expired.
     */
    public function test_an_expired_access_token_is_reported_as_expired(): void
    {
        $token = $this->adminAccessToken($this->createAdmin());

        $this->expireAccessToken();

        $response = $this->adminEndpointGraphQL(self::PROFILE, [], $token);

        $response->assertStatus(401);
        $this->assertSame('access_token_expired', $response->json('errors.0.extensions.code'));
    }

    /**
     * Test an access token stays valid inside the leeway after its expiry.
     */
    public function test_an_access_token_is_accepted_inside_the_leeway(): void
    {
        $token = $this->adminAccessToken($this->createAdmin());

        $this->travel(15 * 60 + 20)->seconds();

        $this->assertNull($this->adminEndpointGraphQL(self::PROFILE, [], $token)->json('errors'));
    }

    /**
     * Test the last-used address and channel are recorded, at most once a minute from one address.
     */
    public function test_last_used_details_are_recorded_and_throttled(): void
    {
        $pair = $this->adminTokenPair($this->createAdmin());

        $this->adminEndpointGraphQL(self::PROFILE, [], $pair->accessToken)->assertOk();

        $session = $pair->session->fresh();
        $firstUse = $session->last_used_at;

        $this->assertSame('127.0.0.1', $session->last_ip_address);
        $this->assertSame('graphql', $session->last_channel);

        $this->travel(30)->seconds();
        $this->adminEndpointGraphQL(self::PROFILE, [], $pair->accessToken)->assertOk();

        $this->assertTrue($firstUse->equalTo($pair->session->fresh()->last_used_at));

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7']);
        $this->adminEndpointGraphQL(self::PROFILE, [], $pair->accessToken)->assertOk();

        $this->assertSame('203.0.113.7', $pair->session->fresh()->last_ip_address);
    }

    /**
     * Test forged access tokens are rejected.
     */
    public function test_forged_access_tokens_are_rejected(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminAccessToken($admin);
        $claims = $this->jwtClaims($token);

        $forged = [
            'a tampered payload' => $this->withTamperedPayload($token, ['sid' => (int) $claims['sid'] + 1]),
            'a signature by another key' => $this->signJwt($claims, [], $this->foreignPrivateKey()),
            'alg none' => $this->unsignedJwt($claims),
            'HS256 signed with the public key' => $this->hs256WithPublicKey($claims),
            'an unknown kid' => $this->signJwt($claims, ['kid' => 'unknown-key']),
            'a wrong aud' => $this->signJwt(array_merge($claims, ['aud' => 'someone-else'])),
        ];

        foreach ($forged as $case => $value) {
            $response = $this->adminEndpointGraphQL(self::PROFILE, [], $value);

            $this->assertSame(401, $response->getStatusCode(), $case.' was not rejected');
            $this->assertSame('unauthenticated', $response->json('errors.0.extensions.code'), $case.' gave the wrong code');
        }

        $this->adminEndpointGraphQL(self::PROFILE, [], $this->signJwt($claims))->assertOk();
    }

    /**
     * Test a refresh token is not accepted as a bearer token.
     */
    public function test_a_refresh_token_is_not_a_bearer_token(): void
    {
        $refreshToken = $this->adminRefreshToken($this->createAdmin());

        $this->adminEndpointGraphQL(self::PROFILE, [], $refreshToken)->assertStatus(401);
    }

    /**
     * Test an API token is refused where a login session is required.
     */
    public function test_an_api_token_cannot_use_the_session_operations(): void
    {
        $apiToken = $this->adminApiToken($this->createAdmin());

        $response = $this->adminEndpointGraphQL('query { adminSessions { edges { node { _id } } } }', [], $apiToken);

        $this->assertSame('access_token_required', $response->json('errors.0.extensions.code'));
    }

    // ========================================================================
    // Refresh
    // ========================================================================

    /**
     * Test refresh rotates the pair, and replaying the old refresh token revokes the session.
     */
    public function test_refresh_rotates_and_detects_reuse(): void
    {
        $admin = $this->createAdmin();
        $pair = $this->adminTokenPair($admin);

        $rotated = $this->refresh($pair->refreshToken)->json('data.createAdminTokenRefresh.adminTokenRefresh');

        $this->assertIsArray($rotated);
        $this->assertNotSame($pair->refreshToken, $rotated['refreshToken']);
        $this->assertSame((int) $pair->session->id, $rotated['session']['id']);

        $this->assertSame('refresh_token_invalid', $this->refresh($pair->refreshToken)->json('errors.0.extensions.code'));

        $this->assertSame(AdminApiSession::REVOKED_REFRESH_REUSE, $pair->session->fresh()->revoked_reason);
        $this->adminEndpointGraphQL(self::PROFILE, [], $rotated['accessToken'])->assertStatus(401);
    }

    /**
     * Test an expired refresh token, and a session past its lifetime, cannot be refreshed.
     */
    public function test_expired_refresh_tokens_and_sessions_cannot_be_refreshed(): void
    {
        $admin = $this->createAdmin();
        $expiring = $this->adminTokenPair($admin);
        $ended = $this->adminTokenPair($this->createAdmin());

        $ended->session->update(['expires_at' => now()->subMinute()]);

        $this->assertSame('refresh_token_invalid', $this->refresh($ended->refreshToken)->json('errors.0.extensions.code'));

        $this->travel(31)->days();

        $this->assertSame('refresh_token_invalid', $this->refresh($expiring->refreshToken)->json('errors.0.extensions.code'));
    }

    // ========================================================================
    // Logout And Sessions
    // ========================================================================

    /**
     * Test logout ends the current session and logout-all ends the rest.
     */
    public function test_logout_and_logout_all(): void
    {
        $admin = $this->createAdmin();
        $first = $this->adminAccessToken($admin);
        $second = $this->adminAccessToken($admin);
        $third = $this->adminAccessToken($admin);

        $logout = $this->adminEndpointGraphQL('mutation { createAdminLogout(input: {}) { adminLogout { success } } }', [], $first);

        $this->assertTrue($logout->json('data.createAdminLogout.adminLogout.success'));
        $this->adminEndpointGraphQL(self::PROFILE, [], $first)->assertStatus(401);
        $this->adminEndpointGraphQL(self::PROFILE, [], $second)->assertOk();

        $all = $this->adminEndpointGraphQL('mutation { createAdminLogoutAll(input: {}) { adminLogoutAll { revoked } } }', [], $second);

        $this->assertSame(2, $all->json('data.createAdminLogoutAll.adminLogoutAll.revoked'));
        $this->adminEndpointGraphQL(self::PROFILE, [], $third)->assertStatus(401);
    }

    /**
     * Test adminSessions lists my live sessions only and deleteAdminSession revokes one of them.
     */
    public function test_sessions_query_and_revoke(): void
    {
        $admin = $this->createAdmin();
        $current = $this->adminTokenPair($admin, ['client_type' => 'web']);
        $lost = $this->adminTokenPair($admin, ['client_type' => 'mobile']);
        $foreign = $this->adminTokenPair($this->createAdmin());

        $nodes = collect($this->adminEndpointGraphQL(
            'query { adminSessions { edges { node { _id clientType isCurrent lastChannel } } } }',
            [],
            $current->accessToken,
        )->json('data.adminSessions.edges'))->pluck('node');

        $this->assertEqualsCanonicalizing([(int) $current->session->id, (int) $lost->session->id], $nodes->pluck('_id')->all());
        $this->assertTrue($nodes->firstWhere('_id', (int) $current->session->id)['isCurrent']);
        $this->assertSame('graphql', $nodes->firstWhere('_id', (int) $current->session->id)['lastChannel']);
        $this->assertSame('mobile', $nodes->firstWhere('_id', (int) $lost->session->id)['clientType']);

        $revoke = $this->adminEndpointGraphQL(
            'mutation ($input: deleteAdminSessionInput!) { deleteAdminSession(input: $input) { adminSession { _id status } } }',
            ['input' => ['id' => '/api/admin/auth/sessions/'.$lost->session->id]],
            $current->accessToken,
        );

        $this->assertSame('revoked', $revoke->json('data.deleteAdminSession.adminSession.status'));
        $this->adminEndpointGraphQL(self::PROFILE, [], $lost->accessToken)->assertStatus(401);

        $foreignRevoke = $this->adminEndpointGraphQL(
            'mutation ($input: deleteAdminSessionInput!) { deleteAdminSession(input: $input) { adminSession { _id } } }',
            ['input' => ['id' => '/api/admin/auth/sessions/'.$foreign->session->id]],
            $current->accessToken,
        );

        $this->assertNotEmpty($foreignRevoke->json('errors'));
        $this->adminEndpointGraphQL(self::PROFILE, [], $foreign->accessToken)->assertOk();
    }

    /**
     * Test another admin's sessions need settings.api_sessions, and revoking them settings.api_sessions.delete.
     */
    public function test_other_admins_sessions_need_the_permissions(): void
    {
        $target = $this->createAdmin();
        $targetToken = $this->adminTokenPair($target);

        $limited = $this->adminAccessToken($this->createAdminWithRolePermissions(['settings', 'settings.users']));
        $viewer = $this->adminAccessToken($this->createAdminWithRolePermissions(['settings', 'settings.api_sessions']));
        $manager = $this->adminAccessToken($this->createAdminWithRolePermissions(['settings', 'settings.api_sessions', 'settings.api_sessions.delete']));

        $query = "query { adminSettingsUserSessions(admin_id: {$target->id}) { edges { node { _id } } } }";

        $this->assertNotEmpty($this->adminEndpointGraphQL($query, [], $limited)->json('errors'));
        $this->assertNotEmpty($this->adminEndpointGraphQL('query { adminApiSessions { edges { node { _id } } } }', [], $limited)->json('errors'));

        $this->assertContains(
            (int) $targetToken->session->id,
            collect($this->adminEndpointGraphQL($query, [], $viewer)->json('data.adminSettingsUserSessions.edges'))->pluck('node._id')->all(),
        );

        $mutation = 'mutation ($input: revokeSessionsAdminSettingsUserInput!) { revokeSessionsAdminSettingsUser(input: $input) { adminSettingsUser { _id message } } }';
        $variables = ['input' => ['id' => '/api/admin/settings/users/'.$target->id]];

        $this->assertNotEmpty($this->adminEndpointGraphQL($mutation, $variables, $viewer)->json('errors'));
        $this->adminEndpointGraphQL(self::PROFILE, [], $targetToken->accessToken)->assertOk();

        $this->assertSame((int) $target->id, $this->adminEndpointGraphQL($mutation, $variables, $manager)->json('data.revokeSessionsAdminSettingsUser.adminSettingsUser._id'));
        $this->adminEndpointGraphQL(self::PROFILE, [], $targetToken->accessToken)->assertStatus(401);
    }

    // ========================================================================
    // Automatic Revocation And Roles
    // ========================================================================

    /**
     * Test a password change through createAdminAccountUpdate keeps this session and revokes the others.
     */
    public function test_a_password_change_revokes_the_other_sessions(): void
    {
        $admin = $this->createAdmin();
        $current = $this->adminAccessToken($admin);
        $other = $this->adminAccessToken($admin);

        $response = $this->adminEndpointGraphQL(
            'mutation ($input: createAdminAccountUpdateInput!) { createAdminAccountUpdate(input: $input) { adminAccountUpdate { passwordChanged revokedSessions } } }',
            ['input' => [
                'name' => $admin->name,
                'password' => 'new-secret-1',
                'passwordConfirmation' => 'new-secret-1',
                'currentPassword' => $this->adminPassword,
            ]],
            $current,
        );

        $this->assertTrue($response->json('data.createAdminAccountUpdate.adminAccountUpdate.passwordChanged'), $response->getContent());
        $this->assertSame(1, $response->json('data.createAdminAccountUpdate.adminAccountUpdate.revokedSessions'));
        $this->adminEndpointGraphQL(self::PROFILE, [], $current)->assertOk();
        $this->adminEndpointGraphQL(self::PROFILE, [], $other)->assertStatus(401);
    }

    /**
     * Test createAdminAccountUpdate needs the current password.
     */
    public function test_the_account_update_needs_the_current_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminEndpointGraphQL(
            'mutation ($input: createAdminAccountUpdateInput!) { createAdminAccountUpdate(input: $input) { adminAccountUpdate { passwordChanged } } }',
            ['input' => [
                'name' => 'Renamed',
                'currentPassword' => 'not-my-password',
            ]],
            $this->adminAccessToken($admin),
        );

        $this->assertSame('invalid_current_password', $response->json('errors.0.extensions.code'));
        $this->assertNotSame('Renamed', $admin->fresh()->name);
    }

    /**
     * Test deactivation rejects a live token, and a role change applies on the next request.
     */
    public function test_deactivation_and_role_changes_apply_on_the_next_request(): void
    {
        $admin = $this->createAdminWithRolePermissions(['settings', 'settings.users']);
        $token = $this->adminAccessToken($admin);

        $users = 'query { adminSettingsUsers(first: 1) { edges { node { _id } } } }';

        $this->assertNull($this->adminEndpointGraphQL($users, [], $token)->json('errors'));

        $admin->role->update(['permissions' => ['dashboard']]);

        $this->assertNotEmpty($this->adminEndpointGraphQL($users, [], $token)->json('errors'));

        $admin->update(['status' => 0]);

        $this->adminEndpointGraphQL(self::PROFILE, [], $token)->assertStatus(401);
    }

    // ========================================================================
    // Password Reset
    // ========================================================================

    /**
     * Test forgot password is public and generic, and reset revokes every session.
     */
    public function test_forgot_and_reset_password(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->createAdmin();
        $token = $this->adminAccessToken($admin);

        $forgot = 'mutation ($input: createAdminForgotPasswordInput!) { createAdminForgotPassword(input: $input) { adminForgotPassword { success message } } }';

        $known = $this->adminEndpointGraphQL($forgot, ['input' => ['email' => $admin->email]])->json('data.createAdminForgotPassword.adminForgotPassword');
        $unknown = $this->adminEndpointGraphQL($forgot, ['input' => ['email' => 'nobody-'.uniqid().'@example.com']])->json('data.createAdminForgotPassword.adminForgotPassword');

        $this->assertSame($known, $unknown);

        $reset = $this->adminEndpointGraphQL(
            'mutation ($input: createAdminResetPasswordInput!) { createAdminResetPassword(input: $input) { adminResetPassword { success } } }',
            ['input' => [
                'token' => Password::broker('admins')->createToken($admin),
                'email' => $admin->email,
                'password' => 'reset-secret-1',
                'passwordConfirmation' => 'reset-secret-1',
            ]],
        );

        $this->assertTrue($reset->json('data.createAdminResetPassword.adminResetPassword.success'), $reset->getContent());
        $this->adminEndpointGraphQL(self::PROFILE, [], $token)->assertStatus(401);
        $this->assertTrue(Hash::check('reset-secret-1', $admin->fresh()->password));
    }

    /**
     * Test a wrong reset token is refused.
     */
    public function test_a_wrong_reset_token_is_refused(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminEndpointGraphQL(
            'mutation ($input: createAdminResetPasswordInput!) { createAdminResetPassword(input: $input) { adminResetPassword { success } } }',
            ['input' => [
                'token' => 'not-a-reset-token',
                'email' => $admin->email,
                'password' => 'reset-secret-1',
                'passwordConfirmation' => 'reset-secret-1',
            ]],
        );

        $this->assertSame('password_reset_token_invalid', $response->json('errors.0.extensions.code'));
        $this->assertFalse(Hash::check('reset-secret-1', $admin->fresh()->password));
    }

    // ========================================================================
    // Audit
    // ========================================================================

    /**
     * Test GraphQL auth events are audited on the graphql channel.
     */
    public function test_auth_events_are_audited_on_the_graphql_channel(): void
    {
        $admin = $this->createAdmin();

        $login = $this->login($admin->email, $this->adminPassword)->json('data.createAdminLogin.adminLogin');

        $this->refresh($login['refreshToken'])->assertOk();

        foreach ([AdminApiAuthAuditor::EVENT_LOGIN, AdminApiAuthAuditor::EVENT_REFRESH] as $event) {
            $this->assertDatabaseHas('admin_api_audits', [
                'event' => $event,
                'user_id' => $admin->id,
                'token_kind' => AdminApiAudit::TOKEN_KIND_SESSION,
                'token_id' => $login['session']['id'],
                'channel' => AdminApiAudit::CHANNEL_GRAPHQL,
            ]);
        }
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
     * Send createAdminLogin.
     */
    protected function login(string $email, string $password, ?string $deviceName = null, ?string $clientType = null): TestResponse
    {
        $input = array_filter([
            'email' => $email,
            'password' => $password,
            'deviceName' => $deviceName,
            'clientType' => $clientType,
        ], fn ($value) => $value !== null);

        return $this->adminEndpointGraphQL(
            'mutation ($input: createAdminLoginInput!) { createAdminLogin(input: $input) { adminLogin { '.self::PAIR_FIELDS.' twoFactorRequired challengeToken challengeTokenExpiresAt } } }',
            ['input' => $input],
        );
    }

    /**
     * Send createAdminTokenRefresh.
     */
    protected function refresh(string $refreshToken): TestResponse
    {
        return $this->adminEndpointGraphQL(
            'mutation ($input: createAdminTokenRefreshInput!) { createAdminTokenRefresh(input: $input) { adminTokenRefresh { '.self::PAIR_FIELDS.' } } }',
            ['input' => ['refreshToken' => $refreshToken]],
        );
    }

    /**
     * Send createAdminTwoFactorVerify with a code or a backup code.
     */
    protected function verifyTwoFactor(string $challengeToken, ?string $code = null, ?string $backupCode = null): TestResponse
    {
        $input = array_filter([
            'challengeToken' => $challengeToken,
            'code' => $code,
            'backupCode' => $backupCode,
        ], fn ($value) => $value !== null);

        return $this->adminEndpointGraphQL(
            'mutation ($input: createAdminTwoFactorVerifyInput!) { createAdminTwoFactorVerify(input: $input) { adminTwoFactorVerify { '.self::PAIR_FIELDS.' } } }',
            ['input' => $input],
        );
    }

    /**
     * Turn two-factor authentication on for an admin and return its secret.
     */
    protected function enableTwoFactor(Admin $admin, array $backupCodes = ['670089']): string
    {
        if (! Schema::hasColumn('admins', 'two_factor_enabled')) {
            $this->markTestSkipped('Two-factor authentication needs a core with the admin two-factor columns.');
        }

        $secret = (new Google2FA)->generateSecretKey();

        $admin->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_enabled' => true,
            'two_factor_backup_codes' => array_map(fn ($code) => Hash::make($code), $backupCodes),
            'two_factor_verified_at' => now(),
        ]);

        return $secret;
    }

    /**
     * A six-digit code that is not the current one for the secret.
     */
    protected function wrongOtp(string $secret): string
    {
        $current = (new Google2FA)->getCurrentOtp($secret);

        return str_pad((string) (((int) $current + 1) % 1000000), 6, '0', STR_PAD_LEFT);
    }
}
