<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\Web;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Webkul\BagistoApi\Admin\Mail\AdminTokenNotification;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Admin\Services\AdminTokenService;
use Webkul\User\Models\Admin;

/**
 * Feature coverage for the admin "API Integration" plugin:
 * token CRUD, the generate/regenerate/revoke lifecycle, the lifecycle
 * email notifications, and the signed login-free revoke link.
 */
class AdminIntegrationTest extends TestCase
{
    protected AdminTokenService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->service = app(AdminTokenService::class);
    }

    /** Create an admin and authenticate as them on the `admin` guard. */
    protected function actingAdmin(): Admin
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /** Build a draft token owned by the given admin. */
    protected function draftToken(Admin $admin): AdminPersonalAccessToken
    {
        return $this->service->createDraft([
            'admin_id'        => $admin->id,
            'name'            => 'Test Integration',
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_ALL,
        ], $admin->id);
    }

    /** Build an already-active token owned by the given admin. */
    protected function activeToken(Admin $admin): AdminPersonalAccessToken
    {
        return $this->service->generate($this->draftToken($admin))['token'];
    }

    public function test_index_requires_admin_authentication(): void
    {
        $this->get(route('admin.integration.index'))
            ->assertRedirect(route('admin.session.create'));
    }

    public function test_index_loads_for_authenticated_admin(): void
    {
        $this->actingAdmin();

        $this->get(route('admin.integration.index'))->assertOk();
    }

    public function test_create_page_loads(): void
    {
        $this->actingAdmin();

        $this->get(route('admin.integration.create'))->assertOk();
    }

    public function test_store_creates_a_draft_token(): void
    {
        $admin = $this->actingAdmin();

        $this->post(route('admin.integration.store'), [
            'name'            => 'Partner ERP',
            'admin_id'        => $admin->id,
            'permission_type' => 'all',
        ])->assertRedirect();

        $this->assertDatabaseHas('admin_personal_access_tokens', [
            'admin_id' => $admin->id,
            'name'     => 'Partner ERP',
            'status'   => AdminPersonalAccessToken::STATUS_DRAFT,
            'token'    => null,
        ]);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $this->actingAdmin();

        $this->post(route('admin.integration.store'), [])
            ->assertSessionHasErrors(['name', 'admin_id', 'permission_type']);
    }

    public function test_store_rejects_admin_who_already_has_a_token(): void
    {
        $admin = $this->actingAdmin();
        $this->draftToken($admin);

        $this->post(route('admin.integration.store'), [
            'name'            => 'Second Integration',
            'admin_id'        => $admin->id,
            'permission_type' => 'all',
        ])->assertSessionHasErrors('admin_id');
    }

    public function test_edit_page_loads(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $this->get(route('admin.integration.edit', $token->id))->assertOk();
    }

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

    public function test_generate_only_works_on_a_draft_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->post(route('admin.integration.generate', $token->id))
            ->assertRedirect(route('admin.integration.edit', $token->id));

        $this->assertSame(1, AdminPersonalAccessToken::where('admin_id', $admin->id)->count());
    }

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

    public function test_regenerate_only_works_on_an_active_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $this->post(route('admin.integration.regenerate', $token->id))
            ->assertRedirect(route('admin.integration.edit', $token->id));

        $this->assertTrue($token->refresh()->isDraft());
    }

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

    public function test_destroy_rejects_an_already_inactive_token(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);
        $this->service->revoke($token, $admin->id);

        $this->deleteJson(route('admin.integration.destroy', $token->id))
            ->assertStatus(400);
    }

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

        $token->refresh();
        $this->assertSame(AdminPersonalAccessToken::STATUS_REVOKED, $token->status);
        $this->assertNull($token->token);
    }

    public function test_revoke_link_without_a_valid_signature_is_rejected(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->activeToken($admin);

        $this->get(route('admin.integration.revoke-via-email', ['id' => $token->id]))
            ->assertStatus(403);

        $this->assertSame(AdminPersonalAccessToken::STATUS_ACTIVE, $token->refresh()->status);
    }

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

    /** Force the module enable flag in the core_config table. */
    protected function setModuleEnabled(bool $enabled): void
    {
        DB::table('core_config')->updateOrInsert(
            ['code' => 'api.integration.settings.enabled', 'channel_code' => null, 'locale_code' => null],
            ['value' => $enabled ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function test_pages_return_404_when_module_disabled(): void
    {
        $this->actingAdmin();
        $this->setModuleEnabled(false);

        $this->get(route('admin.integration.index'))->assertNotFound();
    }

    public function test_pages_load_when_module_enabled(): void
    {
        $this->actingAdmin();
        $this->setModuleEnabled(true);

        $this->get(route('admin.integration.index'))->assertOk();
    }

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

        $this->assertSame(AdminPersonalAccessToken::STATUS_REVOKED, $token->refresh()->status);
    }

    /**
     * Issue a usable plaintext Bearer token bound to the given admin with the
     * supplied `allowed_ips` value.
     */
    protected function tokenWithIps(Admin $admin, ?array $allowedIps): array
    {
        $plain = \Illuminate\Support\Str::random(40);

        $row = AdminPersonalAccessToken::create([
            'admin_id'        => $admin->id,
            'name'            => 'ip-allowlist-test',
            'token'           => hash('sha256', $plain),
            'token_preview'   => substr($plain, 0, 8),
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_ALL,
            'abilities'       => [],
            'expires_at'      => now()->addDay(),
            'status'          => AdminPersonalAccessToken::STATUS_ACTIVE,
            'allowed_ips'     => $allowedIps,
        ]);

        return [$row, $row->id.'|'.$plain];
    }

    public function test_token_with_allowed_ips_accepts_request_from_listed_ip(): void
    {
        $admin = Admin::factory()->create();

        [, $bearer] = $this->tokenWithIps($admin, ['127.0.0.1', '10.0.0.5']);

        $response = $this->getJson('/api/admin/get', [
            'Authorization' => 'Bearer '.$bearer,
        ]);

        $response->assertOk();
    }

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
                'HTTP_ACCEPT'        => 'application/json',
                'REMOTE_ADDR'        => '203.0.113.99',
            ]
        );

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_token_with_empty_or_null_allowed_ips_accepts_any_ip(): void
    {
        $admin = Admin::factory()->create();

        [, $bearerNull] = $this->tokenWithIps($admin, null);

        $other = Admin::factory()->create();
        [, $bearerEmpty] = $this->tokenWithIps($other, []);

        $headers = ['HTTP_ACCEPT' => 'application/json'];

        $r1 = $this->call('GET', '/api/admin/get', [], [], [], $headers + [
            'HTTP_AUTHORIZATION' => 'Bearer '.$bearerNull,
            'REMOTE_ADDR'        => '203.0.113.50',
        ]);
        $this->assertSame(200, $r1->getStatusCode(), 'null allowed_ips should accept any IP');

        $r2 = $this->call('GET', '/api/admin/get', [], [], [], $headers + [
            'HTTP_AUTHORIZATION' => 'Bearer '.$bearerEmpty,
            'REMOTE_ADDR'        => '198.51.100.10',
        ]);
        $this->assertSame(200, $r2->getStatusCode(), 'empty allowed_ips should accept any IP');
    }

    public function test_token_with_cidr_range_in_allowed_ips_matches_ip_in_range(): void
    {
        $admin = Admin::factory()->create();

        [$row] = $this->tokenWithIps($admin, ['10.0.0.0/24']);

        $this->assertTrue($row->isIpAllowed('10.0.0.5'), 'IP inside /24 should be allowed');
        $this->assertTrue($row->isIpAllowed('10.0.0.254'), 'top of /24 should be allowed');
        $this->assertFalse($row->isIpAllowed('10.0.1.5'), 'IP outside /24 should be denied');
        $this->assertFalse($row->isIpAllowed('192.168.0.1'), 'unrelated IP should be denied');
    }

    public function test_isipallowed_supports_ipv6_cidr(): void
    {
        $admin = Admin::factory()->create();

        [$row] = $this->tokenWithIps($admin, ['2001:db8::/32']);

        $this->assertTrue($row->isIpAllowed('2001:db8::1'));
        $this->assertTrue($row->isIpAllowed('2001:db8:1234::abcd'));
        $this->assertFalse($row->isIpAllowed('2001:db9::1'));
    }

    public function test_store_rejects_invalid_ip_in_allowed_ips(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->post(route('admin.integration.store'), [
            'name'            => 'IP Test',
            'admin_id'        => Admin::factory()->create()->id,
            'permission_type' => 'all',
            'allowed_ips'     => ['not-a-real-ip'],
        ]);

        $response->assertSessionHasErrors('allowed_ips.0');
    }

    public function test_update_rejects_invalid_cidr_prefix(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $response = $this->put(route('admin.integration.update', $token->id), [
            'name'            => $token->name,
            'permission_type' => 'all',
            'ip_mode'         => 'restricted',
            'allowed_ips'     => ['10.0.0.0/99'],
        ]);

        $response->assertSessionHasErrors('allowed_ips.0');
    }

    public function test_update_persists_allowed_ips_from_textarea(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);

        $this->put(route('admin.integration.update', $token->id), [
            'name'              => $token->name,
            'permission_type'   => 'all',
            'ip_mode'           => 'restricted',
            'allowed_ips_text'  => "10.0.0.0/24\n203.0.113.1\n2001:db8::/32",
        ])->assertRedirect();

        $token->refresh();

        $this->assertSame(
            ['10.0.0.0/24', '203.0.113.1', '2001:db8::/32'],
            $token->allowed_ips,
        );
    }

    public function test_update_with_ip_mode_any_writes_null_allowed_ips(): void
    {
        $admin = $this->actingAdmin();
        $token = $this->draftToken($admin);
        $token->update(['allowed_ips' => ['10.0.0.0/24']]);

        $this->put(route('admin.integration.update', $token->id), [
            'name'            => $token->name,
            'permission_type' => 'all',
            'ip_mode'         => 'any',
        ])->assertRedirect();

        $this->assertNull($token->refresh()->allowed_ips);
    }
}
