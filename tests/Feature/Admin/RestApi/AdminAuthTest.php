<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

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
     * The REST login path.
     */
    protected const LOGIN = '/api/admin/auth/login';

    /**
     * The REST refresh path.
     */
    protected const REFRESH = '/api/admin/auth/refresh';

    /**
     * The REST profile path, used to prove a token works.
     */
    protected const PROFILE = '/api/admin/get';

    // ========================================================================
    // Login
    // ========================================================================

    /**
     * Test login returns a token pair, the session and the admin.
     */
    public function test_login_returns_a_token_pair_the_session_and_the_admin(): void
    {
        $admin = $this->createAdmin();

        $response = $this->login($admin->email, $this->adminPassword, [
            'device_name' => 'iPhone',
            'client_type' => 'mobile',
        ]);

        $response->assertOk()
            ->assertJsonPath('tokenType', 'Bearer')
            ->assertJsonPath('expiresIn', 900)
            ->assertJsonPath('twoFactorRequired', false)
            ->assertJsonPath('session.name', 'iPhone')
            ->assertJsonPath('session.clientType', 'mobile')
            ->assertJsonPath('admin.id', (int) $admin->id)
            ->assertJsonPath('admin.email', $admin->email);

        $this->assertMatchesRegularExpression('/^\d+\|[0-9a-f]{64}$/', $response->json('refreshToken'));
        $this->assertNotEmpty($response->json('permissions'));

        $claims = $this->jwtClaims($response->json('accessToken'));

        $this->assertSame('access', $claims['typ']);
        $this->assertSame((int) $admin->id, $claims['sub']);
        $this->assertSame($response->json('session.id'), $claims['sid']);
        $this->assertSame('ES256', $this->jwtHeader($response->json('accessToken'))['alg']);

        $this->withBearer('GET', self::PROFILE, $response->json('accessToken'))->assertOk();
    }

    /**
     * Test the access token carries no email, name, role or permissions.
     */
    public function test_the_access_token_carries_no_personal_data_or_permissions(): void
    {
        $admin = $this->createAdmin();

        $claims = $this->jwtClaims($this->login($admin->email, $this->adminPassword)->json('accessToken'));

        $this->assertSame(['iss', 'aud', 'sub', 'typ', 'sid', 'jti', 'iat', 'nbf', 'exp'], array_keys($claims));
    }

    /**
     * Test the client type is stored, and an unknown or missing one is stored as other.
     */
    public function test_client_type_is_stored_on_the_session(): void
    {
        $admin = $this->createAdmin();

        $pos = $this->login($admin->email, $this->adminPassword, ['client_type' => 'pos'])->json('session.id');
        $none = $this->login($admin->email, $this->adminPassword)->json('session.id');

        $this->assertSame('pos', AdminApiSession::query()->find($pos)->client_type);
        $this->assertSame('other', AdminApiSession::query()->find($none)->client_type);
    }

    /**
     * Test a client type outside the allowed list is rejected.
     */
    public function test_an_unknown_client_type_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $this->login($admin->email, $this->adminPassword, ['client_type' => 'fridge'])->assertStatus(422);
    }

    /**
     * Test a wrong password and an unknown email get the same answer.
     */
    public function test_wrong_password_and_unknown_email_get_the_same_answer(): void
    {
        $admin = $this->createAdmin();

        $wrongPassword = $this->login($admin->email, 'not-the-password');
        $unknownEmail = $this->login('nobody-'.uniqid().'@example.com', 'not-the-password');

        $wrongPassword->assertStatus(401)->assertJsonPath('error', 'invalid_credentials');
        $unknownEmail->assertStatus(401)->assertJsonPath('error', 'invalid_credentials');

        $this->assertSame($wrongPassword->json('message'), $unknownEmail->json('message'));
        $this->assertSame(trans('admin::app.settings.users.login-error'), $wrongPassword->json('message'));
        $this->assertNull($wrongPassword->json('accessToken'));
    }

    /**
     * Test an inactive admin cannot log in.
     */
    public function test_an_inactive_admin_cannot_log_in(): void
    {
        $admin = $this->createAdmin(['status' => 0]);

        $this->login($admin->email, $this->adminPassword)
            ->assertStatus(403)
            ->assertJsonPath('error', 'account_inactive')
            ->assertJsonPath('message', trans('admin::app.settings.users.activate-warning'));

        $this->assertSame(0, AdminApiSession::query()->where('admin_id', $admin->id)->count());
    }

    /**
     * Test login validates the email and the password.
     */
    public function test_login_validates_its_input(): void
    {
        $this->login('', '')->assertStatus(422);
        $this->login('not-an-email', 'secret')->assertStatus(422);
        $this->login('admin@example.com', '')->assertStatus(422);
        $this->postJson(self::LOGIN, [
            'email' => 'admin@example.com',
            'password' => 'secret',
            'device_name' => str_repeat('a', 101),
        ])->assertStatus(422);
    }

    /**
     * Test the sixth failed login in a minute is locked out with Retry-After.
     */
    public function test_the_sixth_failed_login_is_locked_out(): void
    {
        $admin = $this->createAdmin();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->login($admin->email, 'wrong-'.$attempt)->assertStatus(401);
        }

        $response = $this->login($admin->email, $this->adminPassword);

        $response->assertStatus(429)->assertJsonPath('error', 'too_many_login_attempts');
        $this->assertGreaterThan(0, (int) $response->headers->get('Retry-After'));

        $this->travel(61)->seconds();

        $this->login($admin->email, $this->adminPassword)->assertOk();
    }

    // ========================================================================
    // Two-Factor Authentication
    // ========================================================================

    /**
     * Test an admin with two-factor enabled gets a challenge and no token.
     */
    public function test_two_factor_login_returns_a_challenge_and_no_token(): void
    {
        $admin = $this->createAdmin();
        $this->enableTwoFactor($admin);

        $response = $this->login($admin->email, $this->adminPassword);

        $response->assertStatus(202)
            ->assertJsonPath('twoFactorRequired', true)
            ->assertJsonPath('accessToken', null)
            ->assertJsonPath('refreshToken', null);

        $this->assertMatchesRegularExpression('/^\d+\|[0-9a-f]+$/', $response->json('challengeToken'));
        $this->assertSame(0, AdminApiSession::query()->where('admin_id', $admin->id)->count());
    }

    /**
     * Test a valid code finishes the login with the device sent at login.
     */
    public function test_a_valid_code_finishes_the_login(): void
    {
        $admin = $this->createAdmin();
        $secret = $this->enableTwoFactor($admin);

        $challenge = $this->login($admin->email, $this->adminPassword, [
            'device_name' => 'POS 3',
            'client_type' => 'pos',
        ])->json('challengeToken');

        $response = $this->postJson('/api/admin/auth/two-factor', [
            'challenge_token' => $challenge,
            'code' => (new Google2FA)->getCurrentOtp($secret),
        ]);

        $response->assertOk()
            ->assertJsonPath('session.name', 'POS 3')
            ->assertJsonPath('session.clientType', 'pos');

        $this->withBearer('GET', self::PROFILE, $response->json('accessToken'))->assertOk();
    }

    /**
     * Test a wrong code is refused.
     */
    public function test_a_wrong_code_is_refused(): void
    {
        $admin = $this->createAdmin();
        $secret = $this->enableTwoFactor($admin);

        $challenge = $this->login($admin->email, $this->adminPassword)->json('challengeToken');

        $this->postJson('/api/admin/auth/two-factor', [
            'challenge_token' => $challenge,
            'code' => $this->wrongOtp($secret),
        ])->assertJsonPath('error', 'invalid_two_factor_code');

        $this->assertSame(0, AdminApiSession::query()->where('admin_id', $admin->id)->count());
    }

    /**
     * Test five wrong codes revoke the challenge.
     */
    public function test_five_wrong_codes_revoke_the_challenge(): void
    {
        $admin = $this->createAdmin();
        $secret = $this->enableTwoFactor($admin);

        $challenge = $this->login($admin->email, $this->adminPassword)->json('challengeToken');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/admin/auth/two-factor', [
                'challenge_token' => $challenge,
                'code' => $this->wrongOtp($secret),
            ]);
        }

        $this->travel(61)->seconds();

        $this->postJson('/api/admin/auth/two-factor', [
            'challenge_token' => $challenge,
            'code' => (new Google2FA)->getCurrentOtp($secret),
        ])->assertJsonPath('error', 'two_factor_challenge_invalid');
    }

    /**
     * Test a backup code works once and is burned.
     */
    public function test_a_backup_code_works_once(): void
    {
        $admin = $this->createAdmin();
        $this->enableTwoFactor($admin, ['670089', '569097']);

        $first = $this->login($admin->email, $this->adminPassword)->json('challengeToken');

        $this->postJson('/api/admin/auth/two-factor', [
            'challenge_token' => $first,
            'backup_code' => '670089',
        ])->assertOk();

        $this->assertCount(1, $admin->fresh()->two_factor_backup_codes);

        $second = $this->login($admin->email, $this->adminPassword)->json('challengeToken');

        $this->postJson('/api/admin/auth/two-factor', [
            'challenge_token' => $second,
            'backup_code' => '670089',
        ])->assertJsonPath('error', 'invalid_two_factor_code');
    }

    /**
     * Test a challenge is single use.
     */
    public function test_a_challenge_is_single_use(): void
    {
        $admin = $this->createAdmin();
        $this->enableTwoFactor($admin, ['670089', '569097']);

        $challenge = $this->login($admin->email, $this->adminPassword)->json('challengeToken');

        $this->postJson('/api/admin/auth/two-factor', [
            'challenge_token' => $challenge,
            'backup_code' => '670089',
        ])->assertOk();

        $this->postJson('/api/admin/auth/two-factor', [
            'challenge_token' => $challenge,
            'backup_code' => '569097',
        ])->assertJsonPath('error', 'two_factor_challenge_invalid');
    }

    /**
     * Test a challenge expires after five minutes.
     */
    public function test_a_challenge_expires_after_five_minutes(): void
    {
        $admin = $this->createAdmin();
        $this->enableTwoFactor($admin, ['670089']);

        $challenge = $this->login($admin->email, $this->adminPassword)->json('challengeToken');

        $this->travel(6)->minutes();

        $this->postJson('/api/admin/auth/two-factor', [
            'challenge_token' => $challenge,
            'backup_code' => '670089',
        ])->assertJsonPath('error', 'two_factor_challenge_invalid');
    }

    // ========================================================================
    // Access Token
    // ========================================================================

    /**
     * Test an expired access token is reported as expired, so the client refreshes.
     */
    public function test_an_expired_access_token_is_reported_as_expired(): void
    {
        $token = $this->adminAccessToken($this->createAdmin());

        $this->expireAccessToken();

        $this->withBearer('GET', self::PROFILE, $token)
            ->assertStatus(401)
            ->assertJsonPath('error', 'access_token_expired');
    }

    /**
     * Test an access token stays valid inside the leeway after its expiry.
     */
    public function test_an_access_token_is_accepted_inside_the_leeway(): void
    {
        $token = $this->adminAccessToken($this->createAdmin());

        $this->travel(15 * 60 + 20)->seconds();

        $this->withBearer('GET', self::PROFILE, $token)->assertOk();
    }

    /**
     * Test the access token of a revoked session is rejected.
     */
    public function test_the_access_token_of_a_revoked_session_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminAccessToken($admin);

        $this->revokeAdminSessions($admin);

        $this->withBearer('GET', self::PROFILE, $token)
            ->assertStatus(401)
            ->assertJsonPath('error', 'unauthenticated');
    }

    /**
     * Test a re-signed copy of a real token's claims is accepted, so the forgery cases below are meaningful.
     */
    public function test_a_token_crafted_with_the_signing_key_is_accepted(): void
    {
        $token = $this->adminAccessToken($this->createAdmin());

        $this->withBearer('GET', self::PROFILE, $this->signJwt($this->jwtClaims($token)))->assertOk();
    }

    /**
     * Test forged, re-signed and mis-addressed access tokens are rejected.
     */
    public function test_forged_access_tokens_are_rejected(): void
    {
        $admin = $this->createAdmin();
        $other = $this->createAdmin();

        $claims = $this->jwtClaims($this->adminAccessToken($admin));

        foreach ($this->forgedTokens($this->adminAccessToken($admin), $claims, $other) as $case => $token) {
            $response = $this->withBearer('GET', self::PROFILE, $token);

            $this->assertSame(401, $response->getStatusCode(), $case.' was not rejected');
            $this->assertSame('unauthenticated', $response->json('error'), $case.' gave the wrong error');
        }
    }

    /**
     * Test a refresh token sent as a bearer token is rejected.
     */
    public function test_a_refresh_token_is_not_a_bearer_token(): void
    {
        $refreshToken = $this->adminRefreshToken($this->createAdmin());

        $this->withBearer('GET', self::PROFILE, $refreshToken)
            ->assertStatus(401)
            ->assertJsonPath('error', 'unauthenticated');
    }

    /**
     * Test an API token cannot be exchanged at the refresh endpoint.
     */
    public function test_an_api_token_is_not_a_refresh_token(): void
    {
        $apiToken = $this->adminApiToken($this->createAdmin());

        $this->postJson(self::REFRESH, ['refresh_token' => $apiToken])
            ->assertStatus(401)
            ->assertJsonPath('error', 'refresh_token_invalid');
    }

    /**
     * Test an API token is refused where a login session is required.
     */
    public function test_an_api_token_cannot_use_the_session_endpoints(): void
    {
        $apiToken = $this->adminApiToken($this->createAdmin());

        $this->withBearer('GET', '/api/admin/auth/sessions', $apiToken)
            ->assertStatus(403)
            ->assertJsonPath('error', 'access_token_required');

        $this->withBearer('POST', '/api/admin/auth/logout', $apiToken)
            ->assertStatus(403)
            ->assertJsonPath('error', 'access_token_required');
    }

    // ========================================================================
    // Refresh
    // ========================================================================

    /**
     * Test refreshing rotates both tokens and the old refresh token stops working.
     */
    public function test_refresh_rotates_the_pair(): void
    {
        $admin = $this->createAdmin();
        $pair = $this->adminTokenPair($admin);

        $response = $this->postJson(self::REFRESH, ['refresh_token' => $pair->refreshToken]);

        $response->assertOk()
            ->assertJsonPath('session.id', (int) $pair->session->id);

        $this->assertNotSame($pair->refreshToken, $response->json('refreshToken'));
        $this->assertNotSame($pair->accessToken, $response->json('accessToken'));

        $this->withBearer('GET', self::PROFILE, $response->json('accessToken'))->assertOk();

        $this->postJson(self::REFRESH, ['refresh_token' => $response->json('refreshToken')])->assertOk();
    }

    /**
     * Test a reused refresh token revokes the whole session.
     */
    public function test_a_reused_refresh_token_revokes_the_session(): void
    {
        $admin = $this->createAdmin();
        $pair = $this->adminTokenPair($admin);

        $rotated = $this->postJson(self::REFRESH, ['refresh_token' => $pair->refreshToken])->assertOk();

        $this->postJson(self::REFRESH, ['refresh_token' => $pair->refreshToken])
            ->assertStatus(401)
            ->assertJsonPath('error', 'refresh_token_invalid');

        $this->assertSame(AdminApiSession::REVOKED_REFRESH_REUSE, $pair->session->fresh()->revoked_reason);

        $this->withBearer('GET', self::PROFILE, $rotated->json('accessToken'))->assertStatus(401);

        $this->postJson(self::REFRESH, ['refresh_token' => $rotated->json('refreshToken')])
            ->assertStatus(401)
            ->assertJsonPath('error', 'refresh_token_invalid');

        $this->assertDatabaseHas('admin_api_audits', [
            'event' => AdminApiAuthAuditor::EVENT_REFRESH_REUSE,
            'token_kind' => AdminApiAudit::TOKEN_KIND_SESSION,
            'token_id' => $pair->session->id,
        ]);
    }

    /**
     * Test an expired refresh token cannot be exchanged.
     */
    public function test_an_expired_refresh_token_is_invalid(): void
    {
        $pair = $this->adminTokenPair($this->createAdmin());

        $this->travel(31)->days();

        $this->postJson(self::REFRESH, ['refresh_token' => $pair->refreshToken])
            ->assertStatus(401)
            ->assertJsonPath('error', 'refresh_token_invalid');
    }

    /**
     * Test a session past its absolute lifetime cannot be refreshed.
     */
    public function test_a_session_past_its_absolute_lifetime_cannot_be_refreshed(): void
    {
        $admin = $this->createAdmin();
        $pair = $this->adminTokenPair($admin);

        $this->expireAdminSessions($admin);

        $this->postJson(self::REFRESH, ['refresh_token' => $pair->refreshToken])
            ->assertStatus(401)
            ->assertJsonPath('error', 'refresh_token_invalid');
    }

    /**
     * Test a malformed or unknown refresh token is invalid.
     */
    public function test_a_malformed_refresh_token_is_invalid(): void
    {
        foreach (['', 'garbage', '999999999|'.str_repeat('a', 64)] as $value) {
            $status = $this->postJson(self::REFRESH, ['refresh_token' => $value])->getStatusCode();

            $this->assertContains($status, [401, 422], "refresh token '{$value}' was accepted");
        }
    }

    /**
     * Test the refresh token is read from the body only, never from a cookie or header.
     */
    public function test_the_refresh_token_is_read_from_the_body_only(): void
    {
        $pair = $this->adminTokenPair($this->createAdmin());

        $this->postJson(self::REFRESH, [], ['Authorization' => 'Bearer '.$pair->refreshToken])
            ->assertStatus(422);
    }

    /**
     * Test auth input in the query string is ignored, and the audited address leaves the query string out.
     */
    public function test_auth_input_in_the_query_string_is_ignored(): void
    {
        $admin = $this->createAdmin();
        $pair = $this->adminTokenPair($admin);

        $this->postJson(self::REFRESH.'?refresh_token='.rawurlencode($pair->refreshToken), [])
            ->assertStatus(422);

        $this->postJson(self::LOGIN.'?password='.rawurlencode($this->adminPassword), ['email' => $admin->email])
            ->assertStatus(422);

        $session = $this->postJson(self::LOGIN.'?client_type=pos', [
            'email' => $admin->email,
            'password' => $this->adminPassword,
        ])->assertOk()->json('session.id');

        $this->assertSame('other', AdminApiSession::query()->find($session)->client_type);
        $this->assertStringNotContainsString(
            '?',
            (string) AdminApiAudit::query()->where('event', AdminApiAuthAuditor::EVENT_LOGIN)->latest('id')->value('url'),
        );
    }

    // ========================================================================
    // Logout And Sessions
    // ========================================================================

    /**
     * Test a logout sent without a JSON body, as a bare curl POST sends it, still ends the session.
     */
    public function test_logout_needs_no_json_body(): void
    {
        $pair = $this->adminTokenPair($this->createAdmin());

        $this->call('POST', '/api/admin/auth/logout', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$pair->accessToken,
            'HTTP_ACCEPT' => 'application/json',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withBearer('GET', self::PROFILE, $pair->accessToken)->assertStatus(401);
    }

    /**
     * Test logout ends the current session only.
     */
    public function test_logout_ends_the_current_session_only(): void
    {
        $admin = $this->createAdmin();
        $current = $this->adminTokenPair($admin);
        $other = $this->adminAccessToken($admin);

        $this->withBearer('POST', '/api/admin/auth/logout', $current->accessToken)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withBearer('GET', self::PROFILE, $current->accessToken)->assertStatus(401);
        $this->withBearer('GET', self::PROFILE, $other)->assertOk();

        $this->postJson(self::REFRESH, ['refresh_token' => $current->refreshToken])->assertStatus(401);
    }

    /**
     * Test logout-all ends every session of the admin and no one else's.
     */
    public function test_logout_all_ends_every_session_of_the_admin(): void
    {
        $admin = $this->createAdmin();
        $first = $this->adminAccessToken($admin);
        $second = $this->adminAccessToken($admin);
        $someoneElse = $this->adminAccessToken($this->createAdmin());

        $this->withBearer('POST', '/api/admin/auth/logout-all', $first)
            ->assertOk()
            ->assertJsonPath('revoked', 2);

        $this->withBearer('GET', self::PROFILE, $first)->assertStatus(401);
        $this->withBearer('GET', self::PROFILE, $second)->assertStatus(401);
        $this->withBearer('GET', self::PROFILE, $someoneElse)->assertOk();
    }

    /**
     * Test logout keeps the admin's API tokens working.
     */
    public function test_logout_all_keeps_api_tokens_working(): void
    {
        $admin = $this->createAdmin();
        $apiToken = $this->adminApiToken($admin);

        $this->withBearer('POST', '/api/admin/auth/logout-all', $this->adminAccessToken($admin))->assertOk();

        $this->withBearer('GET', self::PROFILE, $apiToken)->assertOk();
    }

    /**
     * Test the sessions list shows only the admin's own live sessions and marks the current one.
     */
    public function test_the_sessions_list_is_scoped_to_the_admin(): void
    {
        $admin = $this->createAdmin();
        $current = $this->adminTokenPair($admin, ['name' => 'Web', 'client_type' => 'web']);
        $phone = $this->adminTokenPair($admin, ['name' => 'Phone', 'client_type' => 'mobile']);
        $revoked = $this->adminTokenPair($admin);
        $foreign = $this->adminTokenPair($this->createAdmin());

        $revoked->session->update(['revoked_at' => now(), 'revoked_reason' => 'test']);

        $rows = collect($this->withBearer('GET', '/api/admin/auth/sessions', $current->accessToken)
            ->assertOk()
            ->json('data'));

        $ids = $rows->pluck('id')->all();

        $this->assertEqualsCanonicalizing([(int) $current->session->id, (int) $phone->session->id], $ids);
        $this->assertNotContains((int) $foreign->session->id, $ids);
        $this->assertTrue($rows->firstWhere('id', (int) $current->session->id)['isCurrent']);
        $this->assertFalse($rows->firstWhere('id', (int) $phone->session->id)['isCurrent']);
        $this->assertSame('mobile', $rows->firstWhere('id', (int) $phone->session->id)['clientType']);
    }

    /**
     * Test revoking one of my sessions keeps the others working.
     */
    public function test_revoking_one_of_my_sessions_keeps_the_others(): void
    {
        $admin = $this->createAdmin();
        $current = $this->adminTokenPair($admin);
        $lost = $this->adminTokenPair($admin);

        $this->withBearer('DELETE', '/api/admin/auth/sessions/'.$lost->session->id, $current->accessToken)
            ->assertNoContent();

        $this->withBearer('GET', self::PROFILE, $lost->accessToken)->assertStatus(401);
        $this->withBearer('GET', self::PROFILE, $current->accessToken)->assertOk();
    }

    /**
     * Test another admin's session cannot be revoked through my sessions.
     */
    public function test_another_admins_session_cannot_be_revoked_as_mine(): void
    {
        $mine = $this->adminAccessToken($this->createAdmin());
        $theirs = $this->adminTokenPair($this->createAdmin());

        $this->withBearer('DELETE', '/api/admin/auth/sessions/'.$theirs->session->id, $mine)->assertNotFound();

        $this->withBearer('GET', self::PROFILE, $theirs->accessToken)->assertOk();
    }

    /**
     * Test another admin's sessions need settings.api_sessions to be listed.
     */
    public function test_listing_another_admins_sessions_needs_the_permission(): void
    {
        $target = $this->createAdmin();
        $session = $this->adminTokenPair($target)->session;

        $limited = $this->createAdminWithRolePermissions(['settings', 'settings.users']);
        $allowed = $this->createAdminWithRolePermissions(['settings', 'settings.api_sessions']);

        $this->adminGet($limited, "/api/admin/settings/users/{$target->id}/sessions")->assertForbidden();
        $this->adminGet($limited, '/api/admin/settings/api-sessions')->assertForbidden();

        $this->assertContains(
            (int) $session->id,
            collect($this->adminGet($allowed, "/api/admin/settings/users/{$target->id}/sessions")->assertOk()->json('data'))->pluck('id')->all(),
        );

        $this->assertContains(
            (int) $session->id,
            collect($this->adminGet($allowed, '/api/admin/settings/api-sessions?per_page=50&admin_id='.$target->id)->assertOk()->json('data'))->pluck('id')->all(),
        );
    }

    /**
     * Test revoking another admin's sessions needs settings.api_sessions.delete.
     */
    public function test_revoking_another_admins_sessions_needs_the_delete_permission(): void
    {
        $target = $this->createAdmin();
        $token = $this->adminAccessToken($target);

        $viewer = $this->createAdminWithRolePermissions(['settings', 'settings.api_sessions']);
        $manager = $this->createAdminWithRolePermissions(['settings', 'settings.api_sessions', 'settings.api_sessions.delete']);

        $this->adminPost($viewer, "/api/admin/settings/users/{$target->id}/revoke-sessions")->assertForbidden();
        $this->withBearer('GET', self::PROFILE, $token)->assertOk();

        $this->adminPost($manager, "/api/admin/settings/users/{$target->id}/revoke-sessions")->assertOk();
        $this->withBearer('GET', self::PROFILE, $token)->assertStatus(401);

        $this->assertSame(
            AdminApiSession::REVOKED_BY_ADMIN,
            AdminApiSession::query()->where('admin_id', $target->id)->value('revoked_reason'),
        );
    }

    /**
     * Test the last-used address and channel are recorded, at most once a minute from one address.
     */
    public function test_last_used_details_are_recorded_and_throttled(): void
    {
        $admin = $this->createAdmin();
        $pair = $this->adminTokenPair($admin);

        $this->withBearer('GET', self::PROFILE, $pair->accessToken)->assertOk();

        $session = $pair->session->fresh();
        $firstUse = $session->last_used_at;

        $this->assertSame('127.0.0.1', $session->last_ip_address);
        $this->assertSame('rest', $session->last_channel);

        $this->travel(30)->seconds();
        $this->withBearer('GET', self::PROFILE, $pair->accessToken)->assertOk();

        $this->assertTrue($firstUse->equalTo($pair->session->fresh()->last_used_at));

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7']);
        $this->withBearer('GET', self::PROFILE, $pair->accessToken)->assertOk();

        $this->assertSame('203.0.113.7', $pair->session->fresh()->last_ip_address);

        $this->travel(61)->seconds();
        $this->withBearer('GET', self::PROFILE, $pair->accessToken)->assertOk();

        $this->assertTrue($pair->session->fresh()->last_used_at->greaterThan($firstUse));
    }

    // ========================================================================
    // Automatic Revocation And Roles
    // ========================================================================

    /**
     * Test a password change through the account endpoint revokes the other sessions.
     */
    public function test_a_password_change_revokes_the_other_sessions(): void
    {
        $admin = $this->createAdmin();
        $current = $this->adminAccessToken($admin);
        $other = $this->adminAccessToken($admin);

        $this->withBearer('PUT', '/api/admin/account', $current, [
            'name' => $admin->name,
            'password' => 'new-secret-1',
            'password_confirmation' => 'new-secret-1',
            'current_password' => $this->adminPassword,
        ])->assertOk()->assertJsonPath('revokedSessions', 1);

        $this->withBearer('GET', self::PROFILE, $current)->assertOk();
        $this->withBearer('GET', self::PROFILE, $other)->assertStatus(401);
        $this->assertTrue(Hash::check('new-secret-1', $admin->fresh()->password));
    }

    /**
     * Test the account endpoint needs the current password.
     */
    public function test_the_account_update_needs_the_current_password(): void
    {
        $admin = $this->createAdmin();

        $this->withBearer('PUT', '/api/admin/account', $this->adminAccessToken($admin), [
            'name' => 'Renamed',
            'current_password' => 'not-my-password',
        ])->assertStatus(422)->assertJsonPath('error', 'invalid_current_password');

        $this->assertNotSame('Renamed', $admin->fresh()->name);
    }

    /**
     * Test deactivating an admin rejects their live access tokens.
     */
    public function test_deactivation_rejects_live_access_tokens(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminAccessToken($admin);

        $admin->update(['status' => 0]);

        $this->withBearer('GET', self::PROFILE, $token)->assertStatus(401);

        $admin->update(['status' => 1]);

        $this->withBearer('GET', self::PROFILE, $token)->assertStatus(401);
    }

    /**
     * Test a role change applies on the next request with the same token.
     */
    public function test_a_role_change_applies_on_the_next_request(): void
    {
        $admin = $this->createAdminWithRolePermissions(['settings', 'settings.users']);
        $token = $this->adminAccessToken($admin);

        $this->withBearer('GET', '/api/admin/settings/users', $token)->assertOk();

        $admin->role->update(['permissions' => ['dashboard']]);

        $this->withBearer('GET', '/api/admin/settings/users', $token)->assertForbidden();
    }

    // ========================================================================
    // Password Reset
    // ========================================================================

    /**
     * Test forgot password answers the same for known and unknown emails.
     */
    public function test_forgot_password_gives_a_generic_answer(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->createAdmin();

        $known = $this->postJson('/api/admin/auth/forgot-password', ['email' => $admin->email])->assertOk();
        $unknown = $this->postJson('/api/admin/auth/forgot-password', ['email' => 'nobody-'.uniqid().'@example.com'])->assertOk();

        $this->assertSame($known->json('message'), $unknown->json('message'));
        $this->assertTrue($known->json('success'));
    }

    /**
     * Test a password reset revokes every session.
     */
    public function test_a_password_reset_revokes_every_session(): void
    {
        $admin = $this->createAdmin();
        $firstPair = $this->adminTokenPair($admin);
        $secondPair = $this->adminTokenPair($admin);
        $first = $firstPair->accessToken;
        $second = $secondPair->accessToken;

        $this->postJson('/api/admin/auth/reset-password', [
            'token' => Password::broker('admins')->createToken($admin),
            'email' => $admin->email,
            'password' => 'reset-secret-1',
            'password_confirmation' => 'reset-secret-1',
        ])->assertOk()->assertJsonPath('success', true);

        $this->withBearer('GET', self::PROFILE, $first)->assertStatus(401);
        $this->withBearer('GET', self::PROFILE, $second)->assertStatus(401);

        $this->assertSame(AdminApiSession::REVOKED_PASSWORD_RESET, $firstPair->session->fresh()->revoked_reason);
        $this->assertSame(AdminApiSession::REVOKED_PASSWORD_RESET, $secondPair->session->fresh()->revoked_reason);

        $reasons = AdminApiAudit::query()
            ->where('event', AdminApiAuthAuditor::EVENT_CREDENTIALS_AUTO_REVOKED)
            ->where('user_id', $admin->id)
            ->get()
            ->map(fn ($audit) => $audit->new_values['reason'] ?? null)
            ->all();

        $this->assertContains(AdminApiSession::REVOKED_PASSWORD_RESET, $reasons);
        $this->assertNotContains(AdminApiSession::REVOKED_PASSWORD_CHANGE, $reasons);

        $this->login($admin->email, 'reset-secret-1')->assertOk();
    }

    /**
     * Test a wrong reset token is refused.
     */
    public function test_a_wrong_reset_token_is_refused(): void
    {
        $admin = $this->createAdmin();

        $this->postJson('/api/admin/auth/reset-password', [
            'token' => 'not-a-reset-token',
            'email' => $admin->email,
            'password' => 'reset-secret-1',
            'password_confirmation' => 'reset-secret-1',
        ])->assertStatus(422)->assertJsonPath('error', 'password_reset_token_invalid');
    }

    // ========================================================================
    // Audit
    // ========================================================================

    /**
     * Test each authentication event writes an audit row with its token kind and channel.
     */
    public function test_auth_events_are_audited_with_token_kind_and_channel(): void
    {
        $admin = $this->createAdmin();

        $this->login($admin->email, 'wrong-password')->assertStatus(401);
        $login = $this->login($admin->email, $this->adminPassword)->assertOk();

        $refreshed = $this->postJson(self::REFRESH, ['refresh_token' => $login->json('refreshToken')])->assertOk();

        $this->withBearer('POST', '/api/admin/auth/logout', $refreshed->json('accessToken'))->assertOk();

        $sessionId = $login->json('session.id');

        foreach ([AdminApiAuthAuditor::EVENT_LOGIN, AdminApiAuthAuditor::EVENT_REFRESH, AdminApiAuthAuditor::EVENT_LOGOUT] as $event) {
            $this->assertDatabaseHas('admin_api_audits', [
                'event' => $event,
                'user_id' => $admin->id,
                'token_kind' => AdminApiAudit::TOKEN_KIND_SESSION,
                'token_id' => $sessionId,
                'channel' => AdminApiAudit::CHANNEL_REST,
            ]);
        }

        $this->assertDatabaseHas('admin_api_audits', [
            'event' => AdminApiAuthAuditor::EVENT_LOGIN_FAILED,
            'user_id' => $admin->id,
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
     * Send a REST login.
     */
    protected function login(string $email, string $password, array $extra = []): TestResponse
    {
        return $this->postJson(self::LOGIN, array_merge([
            'email' => $email,
            'password' => $password,
        ], $extra));
    }

    /**
     * Turn two-factor authentication on for an admin and return its secret.
     */
    protected function enableTwoFactor(Admin $admin, array $backupCodes = ['670089', '569097']): string
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

    /**
     * Access tokens that must be rejected, each built from a real token's claims.
     *
     * @return array<string, string>
     */
    protected function forgedTokens(string $token, array $claims, Admin $other): array
    {
        return [
            'a tampered payload' => $this->withTamperedPayload($token, ['sub' => (int) $other->id]),
            'a signature by another key' => $this->signJwt($claims, [], $this->foreignPrivateKey()),
            'alg none' => $this->unsignedJwt($claims),
            'HS256 signed with the public key' => $this->hs256WithPublicKey($claims),
            'an unknown kid' => $this->signJwt($claims, ['kid' => 'unknown-key']),
            'a wrong aud' => $this->signJwt(array_merge($claims, ['aud' => 'someone-else'])),
            'a wrong iss' => $this->signJwt(array_merge($claims, ['iss' => 'https://evil.example.com'])),
            'a wrong typ claim' => $this->signJwt(array_merge($claims, ['typ' => 'refresh'])),
            'a wrong typ header' => $this->signJwt($claims, ['typ' => 'at+jwt']),
            'a future nbf' => $this->signJwt(array_merge($claims, ['nbf' => time() + 3600])),
            'no session id' => $this->signJwt(array_diff_key($claims, ['sid' => true])),
            'another admin as subject' => $this->signJwt(array_merge($claims, ['sub' => (int) $other->id])),
            'an unknown session' => $this->signJwt(array_merge($claims, ['sid' => 999999999])),
        ];
    }
}
