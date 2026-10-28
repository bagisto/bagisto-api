<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Console\Scheduling\Schedule;
use Webkul\BagistoApi\Admin\Models\AdminApiRefreshToken;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Models\AdminApiTwoFactorChallenge;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class AdminAuthPruneTest extends AdminApiTestCase
{
    /**
     * Test expired refresh tokens are deleted while live ones, used or not, are kept.
     */
    public function test_expired_refresh_tokens_are_deleted(): void
    {
        $admin = $this->createAdmin();

        $expired = $this->adminTokenPair($admin);
        $live = $this->adminTokenPair($admin);

        $this->postJson('/api/admin/auth/refresh', ['refresh_token' => $live->refreshToken])->assertOk();

        AdminApiRefreshToken::query()->whereKey($expired->refreshTokenId)->update(['expires_at' => now()->subMinute()]);

        $this->artisan('bagisto-api:prune-admin-tokens')->assertSuccessful();

        $this->assertNull(AdminApiRefreshToken::query()->find($expired->refreshTokenId));
        $this->assertNotNull(AdminApiRefreshToken::query()->find($live->refreshTokenId), 'a used refresh token was deleted before it expired, which defeats reuse detection');
    }

    /**
     * Test sessions ended more than the retention period ago are deleted and the rest are kept.
     */
    public function test_long_ended_sessions_are_deleted(): void
    {
        $admin = $this->createAdmin();

        $active = $this->adminTokenPair($admin)->session;
        $recentlyRevoked = $this->adminTokenPair($admin)->session;
        $longRevoked = $this->adminTokenPair($admin)->session;
        $longExpired = $this->adminTokenPair($admin)->session;

        $recentlyRevoked->update(['revoked_at' => now()->subDays(5), 'revoked_reason' => 'logout']);
        $longRevoked->update(['revoked_at' => now()->subDays(31), 'revoked_reason' => 'logout']);
        $longExpired->update(['expires_at' => now()->subDays(31), 'refresh_expires_at' => now()->subDays(31)]);

        $this->artisan('bagisto-api:prune-admin-tokens')->assertSuccessful();

        $this->assertNotNull(AdminApiSession::query()->find($active->id));
        $this->assertNotNull(AdminApiSession::query()->find($recentlyRevoked->id));
        $this->assertNull(AdminApiSession::query()->find($longRevoked->id));
        $this->assertNull(AdminApiSession::query()->find($longExpired->id));
    }

    /**
     * Test expired two-factor challenges are deleted.
     */
    public function test_expired_two_factor_challenges_are_deleted(): void
    {
        $admin = $this->createAdmin();

        $expired = AdminApiTwoFactorChallenge::query()->create([
            'admin_id' => $admin->id,
            'token' => hash('sha256', 'expired'),
            'expires_at' => now()->subMinute(),
        ]);

        $pending = AdminApiTwoFactorChallenge::query()->create([
            'admin_id' => $admin->id,
            'token' => hash('sha256', 'pending'),
            'expires_at' => now()->addMinutes(4),
        ]);

        $this->artisan('bagisto-api:prune-admin-tokens')->assertSuccessful();

        $this->assertNull(AdminApiTwoFactorChallenge::query()->find($expired->id));
        $this->assertNotNull(AdminApiTwoFactorChallenge::query()->find($pending->id));
    }

    /**
     * Test expired and revoked API tokens are kept for the audit trail.
     */
    public function test_api_tokens_are_kept_for_the_audit_trail(): void
    {
        $admin = $this->createAdmin();

        $expired = $this->issueAdminApiToken($admin, 'localhost', now()->addDay())->token;
        $revoked = $this->issueAdminApiToken($admin)->token;

        $revoked->update(['revoked_at' => now()->subDays(100), 'revoked_reason' => AdminApiToken::REVOKED_BY_ADMIN]);

        $this->travel(100)->days();

        $this->artisan('bagisto-api:prune-admin-tokens')->assertSuccessful();

        $this->assertNotNull(AdminApiToken::query()->find($expired->id));
        $this->assertNotNull(AdminApiToken::query()->find($revoked->id));
    }

    /**
     * Test the prune command is scheduled hourly.
     */
    public function test_the_prune_command_is_scheduled_hourly(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'bagisto-api:prune-admin-tokens'));

        $this->assertNotNull($event, 'bagisto-api:prune-admin-tokens is not scheduled');
        $this->assertSame('0 * * * *', $event->expression);
    }
}
