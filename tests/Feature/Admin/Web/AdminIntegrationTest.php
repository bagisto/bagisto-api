<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\Web;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;
use Webkul\BagistoApi\Admin\Mail\AdminTokenNotification;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Admin\Services\AdminTokenService;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class AdminIntegrationTest extends TestCase
{
    /**
     * The admin token service under test.
     */
    protected AdminTokenService $service;

    /**
     * Fake the mailer and resolve the admin token service.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->service = app(AdminTokenService::class);
    }

    /**
     * The integration index redirects a guest to the admin login page.
     */
    public function test_index_requires_admin_authentication(): void
    {
        $this->get(route('admin.integration.index'))
            ->assertRedirect(route('admin.session.create'));
    }

    /**
     * The integration index redirects an admin to the token listing.
     */
    public function test_index_redirects_to_tokens(): void
    {
        $this->actingAdmin();

        $this->get(route('admin.integration.index'))
            ->assertRedirect(route('admin.integration.token.index'));
    }

    /**
     * The token listing loads for an authenticated admin.
     */
    public function test_index_loads_for_authenticated_admin(): void
    {
        $this->actingAdmin();

        $this->get(route('admin.integration.token.index'))->assertOk();
    }

    /**
     * The create page loads for an authenticated admin.
     */
    public function test_create_page_loads(): void
    {
        $this->actingAdmin();

        $this->get(route('admin.integration.create'))->assertOk();
    }

    /**
     * Storing an integration creates a draft token with no secret.
     */
    public function test_store_creates_a_draft_token(): void
    {
        $admin = $this->actingAdmin();

        $this->post(route('admin.integration.store'), [
            'name' => 'Partner ERP',
            'admin_id' => $admin->id,
            'permission_type' => 'all',
        ])->assertRedirect();

        $this->assertDatabaseHas('admin_personal_access_tokens', [
            'admin_id' => $admin->id,
            'name' => 'Partner ERP',
            'status' => AdminPersonalAccessToken::STATUS_DRAFT,
            'token' => null,
        ]);
    }

    /**
     * Storing an integration without the required fields fails validation.
     */
    public function test_store_validation_fails_without_required_fields(): void
    {
        $this->actingAdmin();

        $this->post(route('admin.integration.store'), [])
            ->assertSessionHasErrors(['name', 'admin_id', 'permission_type']);
    }

    /**
     * Storing a second integration for an admin who already has a token is rejected.
     */
    public function test_store_rejects_admin_who_already_has_a_token(): void
    {
        $admin = $this->actingAdmin();
        $this->draftToken($admin);

        $this->post(route('admin.integration.store'), [
            'name' => 'Second Integration',
            'admin_id' => $admin->id,
            'permission_type' => 'all',
        ])->assertSessionHasErrors('admin_id');
    }

    /**
     * The edit page loads for an existing draft token.
     */
    public function test_edit_page_loads(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $this->get(route('admin.integration.edit', $token->id))->assertOk();
    }

    /**
     * Generating a draft token activates it and stores the hashed secret and preview.
     */
    public function test_generate_activates_a_draft_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $this->post(route('admin.integration.generate', $token->id))
            ->assertRedirect(route('admin.integration.edit', $token->id));

        $token->refresh();

        $this->assertSame(AdminPersonalAccessToken::STATUS_ACTIVE, $token->status);
        $this->assertNotNull($token->token);
        $this->assertNotNull($token->token_preview);
    }

    /**
     * Generating a token queues the generated email to the owning admin.
     */
    public function test_generate_sends_generated_email_to_owner(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $this->post(route('admin.integration.generate', $token->id));

        Mail::assertQueued(
            AdminTokenNotification::class,
            fn ($mail) => $mail->event === AdminTokenNotification::EVENT_GENERATED
                && $mail->hasTo($admin->email)
        );
    }

    /**
     * Generating an already-active token creates no new token.
     */
    public function test_generate_only_works_on_a_draft_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->post(route('admin.integration.generate', $token->id))
            ->assertRedirect(route('admin.integration.edit', $token->id));

        $this->assertSame(1, AdminPersonalAccessToken::where('admin_id', $admin->id)->count());
    }

    /**
     * Regenerating a token retires the old one and links it to a new active token.
     */
    public function test_regenerate_supersedes_the_old_token(): void
    {
        $admin = $this->actingAdmin();
        $oldToken = $this->activeToken($admin);

        $this->post(route('admin.integration.regenerate', $oldToken->id))
            ->assertRedirect();

        $oldToken->refresh();

        $this->assertSame(AdminPersonalAccessToken::STATUS_REGENERATED, $oldToken->status);
        $this->assertNull($oldToken->token);
        $this->assertNotNull($oldToken->regenerated_to_id);

        $newToken = AdminPersonalAccessToken::find($oldToken->regenerated_to_id);
        $this->assertSame(AdminPersonalAccessToken::STATUS_ACTIVE, $newToken->status);
        $this->assertNotNull($newToken->token);
    }

    /**
     * Regenerating a token queues the regenerated email to the owning admin.
     */
    public function test_regenerate_sends_regenerated_email(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->post(route('admin.integration.regenerate', $token->id));

        Mail::assertQueued(
            AdminTokenNotification::class,
            fn ($mail) => $mail->event === AdminTokenNotification::EVENT_REGENERATED
                && $mail->hasTo($admin->email)
        );
    }

    /**
     * Regenerating a draft token leaves it a draft.
     */
    public function test_regenerate_only_works_on_an_active_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $this->post(route('admin.integration.regenerate', $token->id))
            ->assertRedirect(route('admin.integration.edit', $token->id));

        $this->assertTrue($token->refresh()->isDraft());
    }

    /**
     * Destroying an active token revokes it and clears the secret.
     */
    public function test_destroy_revokes_an_active_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->deleteJson(route('admin.integration.destroy', $token->id))
            ->assertOk();

        $token->refresh();

        $this->assertSame(AdminPersonalAccessToken::STATUS_REVOKED, $token->status);
        $this->assertNull($token->token);
        $this->assertNotNull($token->revoked_at);
    }

    /**
     * Destroying a token queues the revoked email to the owning admin.
     */
    public function test_destroy_sends_revoked_email(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->deleteJson(route('admin.integration.destroy', $token->id));

        Mail::assertQueued(
            AdminTokenNotification::class,
            fn ($mail) => $mail->event === AdminTokenNotification::EVENT_REVOKED
                && $mail->hasTo($admin->email)
        );
    }

    /**
     * Destroying an already revoked token returns 400.
     */
    public function test_destroy_rejects_an_already_inactive_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);
        $this->service->revoke($token, $admin->id);

        $this->deleteJson(route('admin.integration.destroy', $token->id))
            ->assertStatus(400);
    }

    /**
     * The signed email link confirms and then revokes a token without an admin login.
     */
    public function test_signed_revoke_link_revokes_the_token_without_login(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        app('auth')->guard('admin')->logout();

        $url = URL::temporarySignedRoute(
            'admin.integration.revoke-via-email',
            now()->addDays(7),
            ['id' => $token->id]
        );

        $this->get($url)->assertOk();

        $this->assertSame(AdminPersonalAccessToken::STATUS_ACTIVE, $token->refresh()->status);

        $confirmUrl = URL::temporarySignedRoute(
            'admin.integration.revoke-via-email.perform',
            now()->addHour(),
            ['id' => $token->id]
        );

        $this->post($confirmUrl)->assertOk();

        $token->refresh();
        $this->assertSame(AdminPersonalAccessToken::STATUS_REVOKED, $token->status);
        $this->assertNull($token->token);
    }

    /**
     * Opening the signed revoke link shows a confirmation form and leaves the token active.
     */
    public function test_signed_revoke_get_renders_a_confirmation_form_and_does_not_revoke(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $url = URL::temporarySignedRoute(
            'admin.integration.revoke-via-email',
            now()->addDays(7),
            ['id' => $token->id]
        );

        $this->get($url)->assertOk()->assertSee('<form', false)->assertSee('revoke-via-email', false);

        $this->assertSame(AdminPersonalAccessToken::STATUS_ACTIVE, $token->refresh()->status);
    }

    /**
     * Posting to the revoke endpoint without a signature returns 403.
     */
    public function test_unsigned_revoke_post_is_rejected(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->post(route('admin.integration.revoke-via-email.perform', ['id' => $token->id]))->assertStatus(403);

        $this->assertSame(AdminPersonalAccessToken::STATUS_ACTIVE, $token->refresh()->status);
    }

    /**
     * Opening the revoke link without a valid signature returns 403.
     */
    public function test_revoke_link_without_a_valid_signature_is_rejected(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->get(route('admin.integration.revoke-via-email', ['id' => $token->id]))
            ->assertStatus(403);

        $this->assertSame(AdminPersonalAccessToken::STATUS_ACTIVE, $token->refresh()->status);
    }

    /**
     * Opening the signed revoke link for an already revoked token still succeeds.
     */
    public function test_signed_revoke_link_on_an_inactive_token_is_idempotent(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);
        $this->service->revoke($token, $admin->id);

        $url = URL::temporarySignedRoute(
            'admin.integration.revoke-via-email',
            now()->addDays(7),
            ['id' => $token->id]
        );

        $this->get($url)->assertOk();
    }

    /**
     * The generated email carries the revoke link but never the plaintext token.
     */
    public function test_generated_email_contains_revoke_link_but_no_plaintext_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $html = (new AdminTokenNotification(
            $token,
            AdminTokenNotification::EVENT_GENERATED,
            '203.0.113.45'
        ))->render();

        $this->assertStringContainsString('revoke-via-email', $html);
        $this->assertDoesNotMatchRegularExpression('/\b\d+\|[A-Za-z0-9]{40}\b/', $html);
    }

    /**
     * The revoked email carries no revoke link.
     */
    public function test_revoked_email_has_no_revoke_link(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $html = (new AdminTokenNotification(
            $token,
            AdminTokenNotification::EVENT_REVOKED,
            null
        ))->render();

        $this->assertStringNotContainsString('revoke-via-email', $html);
    }

    /**
     * The integration pages return 404 while the module is disabled.
     */
    public function test_pages_return_404_when_module_disabled(): void
    {
        $this->actingAdmin();
        $this->setModuleEnabled(false);

        $this->get(route('admin.integration.index'))->assertNotFound();
    }

    /**
     * The integration pages load while the module is enabled.
     */
    public function test_pages_load_when_module_enabled(): void
    {
        $this->actingAdmin();
        $this->setModuleEnabled(true);

        $this->get(route('admin.integration.token.index'))->assertOk();
    }

    /**
     * The signed revoke link still revokes a token while the module is disabled.
     */
    public function test_signed_revoke_link_works_even_when_module_disabled(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->setModuleEnabled(false);

        $url = URL::temporarySignedRoute(
            'admin.integration.revoke-via-email',
            now()->addDays(7),
            ['id' => $token->id]
        );

        $this->get($url)->assertOk();

        $confirmUrl = URL::temporarySignedRoute(
            'admin.integration.revoke-via-email.perform',
            now()->addHour(),
            ['id' => $token->id]
        );

        $this->post($confirmUrl)->assertOk();

        $this->assertSame(AdminPersonalAccessToken::STATUS_REVOKED, $token->refresh()->status);
    }

    /**
     * A token restricted to listed IPs accepts a request from a listed IP.
     */
    public function test_token_with_allowed_ips_accepts_request_from_listed_ip(): void
    {
        $admin = Admin::factory()->create();

        [, $bearer] = $this->tokenWithIps($admin, ['127.0.0.1', '10.0.0.5']);

        $response = $this->getJson('/api/admin/get', [
            'Authorization' => 'Bearer '.$bearer,
        ]);

        $response->assertOk();
    }

    /**
     * A token restricted to listed IPs rejects a request from an unlisted IP.
     */
    public function test_token_with_allowed_ips_rejects_request_from_unlisted_ip(): void
    {
        $admin = Admin::factory()->create();

        [, $bearer] = $this->tokenWithIps($admin, ['10.0.0.5']);

        $response = $this->call(
            'GET',
            '/api/admin/get',
            [],
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$bearer,
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => '203.0.113.99',
            ]
        );

        $this->assertSame(401, $response->getStatusCode());
    }

    /**
     * A token with null or empty `allowed_ips` accepts a request from any IP.
     */
    public function test_token_with_empty_or_null_allowed_ips_accepts_any_ip(): void
    {
        $admin = Admin::factory()->create();

        [, $bearerNull] = $this->tokenWithIps($admin, null);

        $other = Admin::factory()->create();
        [, $bearerEmpty] = $this->tokenWithIps($other, []);

        $headers = ['HTTP_ACCEPT' => 'application/json'];

        $r1 = $this->call('GET', '/api/admin/get', [], [], [], $headers + [
            'HTTP_AUTHORIZATION' => 'Bearer '.$bearerNull,
            'REMOTE_ADDR' => '203.0.113.50',
        ]);
        $this->assertSame(200, $r1->getStatusCode(), 'null allowed_ips should accept any IP');

        $r2 = $this->call('GET', '/api/admin/get', [], [], [], $headers + [
            'HTTP_AUTHORIZATION' => 'Bearer '.$bearerEmpty,
            'REMOTE_ADDR' => '198.51.100.10',
        ]);
        $this->assertSame(200, $r2->getStatusCode(), 'empty allowed_ips should accept any IP');
    }

    /**
     * An IPv4 CIDR range in `allowed_ips` allows addresses inside it and denies those outside.
     */
    public function test_token_with_cidr_range_in_allowed_ips_matches_ip_in_range(): void
    {
        $admin = Admin::factory()->create();

        [$row] = $this->tokenWithIps($admin, ['10.0.0.0/24']);

        $this->assertTrue($row->isIpAllowed('10.0.0.5'), 'IP inside /24 should be allowed');
        $this->assertTrue($row->isIpAllowed('10.0.0.254'), 'top of /24 should be allowed');
        $this->assertFalse($row->isIpAllowed('10.0.1.5'), 'IP outside /24 should be denied');
        $this->assertFalse($row->isIpAllowed('192.168.0.1'), 'unrelated IP should be denied');
    }

    /**
     * An IPv6 CIDR range in `allowed_ips` allows addresses inside it and denies those outside.
     */
    public function test_isipallowed_supports_ipv6_cidr(): void
    {
        $admin = Admin::factory()->create();

        [$row] = $this->tokenWithIps($admin, ['2001:db8::/32']);

        $this->assertTrue($row->isIpAllowed('2001:db8::1'));
        $this->assertTrue($row->isIpAllowed('2001:db8:1234::abcd'));
        $this->assertFalse($row->isIpAllowed('2001:db9::1'));
    }

    /**
     * Storing an integration with an invalid IP in `allowed_ips` fails validation.
     */
    public function test_store_rejects_invalid_ip_in_allowed_ips(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->post(route('admin.integration.store'), [
            'name' => 'IP Test',
            'admin_id' => Admin::factory()->create()->id,
            'permission_type' => 'all',
            'allowed_ips' => ['not-a-real-ip'],
        ]);

        $response->assertSessionHasErrors('allowed_ips.0');
    }

    /**
     * Updating an integration with an out-of-range CIDR prefix fails validation.
     */
    public function test_update_rejects_invalid_cidr_prefix(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $response = $this->put(route('admin.integration.update', $token->id), [
            'name' => $token->name,
            'permission_type' => 'all',
            'ip_mode' => 'restricted',
            'allowed_ips' => ['10.0.0.0/99'],
        ]);

        $response->assertSessionHasErrors('allowed_ips.0');
    }

    /**
     * Updating an integration stores each line of the allowed IPs textarea.
     */
    public function test_update_persists_allowed_ips_from_textarea(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $this->put(route('admin.integration.update', $token->id), [
            'name' => $token->name,
            'permission_type' => 'all',
            'ip_mode' => 'restricted',
            'allowed_ips_text' => "10.0.0.0/24\n203.0.113.1\n2001:db8::/32",
        ])->assertRedirect();

        $token->refresh();

        $this->assertSame(
            ['10.0.0.0/24', '203.0.113.1', '2001:db8::/32'],
            $token->allowed_ips,
        );
    }

    /**
     * Updating an integration with the any IP mode clears `allowed_ips` to null.
     */
    public function test_update_with_ip_mode_any_writes_null_allowed_ips(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);
        $token->update(['allowed_ips' => ['10.0.0.0/24']]);

        $this->put(route('admin.integration.update', $token->id), [
            'name' => $token->name,
            'permission_type' => 'all',
            'ip_mode' => 'any',
        ])->assertRedirect();

        $this->assertNull($token->refresh()->allowed_ips);
    }

    /**
     * An admin with a custom role may not issue a token for an admin whose role is wider than theirs.
     */
    public function test_custom_admin_cannot_create_a_token_for_a_wider_admin(): void
    {
        $role = Role::create([
            'name' => 'Integrator '.Str::random(6),
            'description' => 'd',
            'permission_type' => 'custom',
            'permissions' => ['integration', 'integration.view', 'integration.create', 'integration.generate'],
        ]);

        $superAdmin = Admin::factory()->create(['role_id' => 1]);

        $this->actingAs(Admin::factory()->create(['role_id' => $role->id]), 'admin');

        $this->post(route('admin.integration.store'), [
            'name' => 'Escalation',
            'admin_id' => $superAdmin->id,
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_ALL,
        ])->assertSessionHasErrors('admin_id');

        expect(AdminPersonalAccessToken::where('admin_id', $superAdmin->id)->exists())->toBeFalse();

        $this->post(route('admin.integration.generate', $this->draftToken($superAdmin)->id))->assertForbidden();
    }

    /**
     * Create an admin and authenticate as them on the `admin` guard.
     */
    protected function actingAdmin(): Admin
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Build a draft token owned by the given admin.
     */
    protected function draftToken(Admin $admin): AdminPersonalAccessToken
    {
        return $this->service->createDraft([
            'admin_id' => $admin->id,
            'name' => 'Test Integration',
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_ALL,
        ], $admin->id);
    }

    /**
     * Build an already-active token owned by the given admin.
     */
    protected function activeToken(Admin $admin): AdminPersonalAccessToken
    {
        return $this->service->generate($this->draftToken($admin))['token'];
    }

    /**
     * Force the module enable flag in the core_config table.
     */
    protected function setModuleEnabled(bool $enabled): void
    {
        DB::table('core_config')->updateOrInsert(
            [
                'code' => 'api.integration.settings.enabled',
                'channel_code' => null,
                'locale_code' => null,
            ], [
                'value' => $enabled ? '1' : '0',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Issue an active all-access token for the admin with the given `allowed_ips` value.
     * Returns the token row and its plaintext bearer string.
     */
    protected function tokenWithIps(Admin $admin, ?array $allowedIps): array
    {
        $plain = Str::random(40);

        $row = AdminPersonalAccessToken::create([
            'admin_id' => $admin->id,
            'name' => 'ip-allowlist-test',
            'token' => hash('sha256', $plain),
            'token_preview' => substr($plain, 0, 8),
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_ALL,
            'abilities' => [],
            'expires_at' => now()->addDay(),
            'status' => AdminPersonalAccessToken::STATUS_ACTIVE,
            'allowed_ips' => $allowedIps,
        ]);

        return [$row, $row->id.'|'.$plain];
    }
}
