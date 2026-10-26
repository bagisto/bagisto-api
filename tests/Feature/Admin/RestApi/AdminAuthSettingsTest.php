<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminApiTwoFactorChallenge;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\CoreConfig;
use Webkul\User\Models\Admin;

class AdminAuthSettingsTest extends AdminApiTestCase
{
    /**
     * The REST login path.
     */
    protected const LOGIN = '/api/admin/auth/login';

    /**
     * An admin path whose response carries the security headers.
     */
    protected const PROFILE = '/api/admin/get';

    // ========================================================================
    // Settings
    // ========================================================================

    /**
     * Test every limit falls back to its default when nothing is configured.
     */
    public function test_limits_default_when_nothing_is_configured(): void
    {
        $settings = app(AdminApiAuthSettings::class);

        $this->assertSame(60, $settings->unauthenticatedRequestsPerMinute());
        $this->assertSame(5, $settings->loginMaxAttempts());
        $this->assertSame(60, $settings->loginLockoutSeconds());
        $this->assertSame(5, $settings->twoFactorChallengeLifetime());
        $this->assertSame(5, $settings->twoFactorMaxFailures());
        $this->assertSame(5, $settings->passwordRequestsPerMinute());
        $this->assertSame(30, $settings->pruneAfterDays());
        $this->assertNull($settings->auditRetentionDays());
    }

    /**
     * Test a configured limit is used when it is inside its range.
     */
    public function test_a_configured_limit_inside_its_range_is_used(): void
    {
        $this->configure([
            'rate_limits.unauthenticated_requests_per_minute' => 200,
            'login_protection.login_max_attempts' => 10,
            'login_protection.login_lockout_seconds' => 300,
            'login_protection.two_factor_challenge_lifetime' => 10,
            'login_protection.two_factor_max_failures' => 8,
            'login_protection.password_requests_per_minute' => 3,
            'retention.session_prune_days' => 7,
            'retention.audit_retention_days' => 90,
        ]);

        $settings = app(AdminApiAuthSettings::class);

        $this->assertSame(200, $settings->unauthenticatedRequestsPerMinute());
        $this->assertSame(10, $settings->loginMaxAttempts());
        $this->assertSame(300, $settings->loginLockoutSeconds());
        $this->assertSame(10, $settings->twoFactorChallengeLifetime());
        $this->assertSame(8, $settings->twoFactorMaxFailures());
        $this->assertSame(3, $settings->passwordRequestsPerMinute());
        $this->assertSame(7, $settings->pruneAfterDays());
        $this->assertSame(90, $settings->auditRetentionDays());
    }

    /**
     * Test a configured limit outside its range is clamped to the nearest bound.
     */
    public function test_a_configured_limit_outside_its_range_is_clamped(): void
    {
        $this->configure([
            'rate_limits.session_requests_per_minute' => 500000,
            'rate_limits.unauthenticated_requests_per_minute' => 1,
            'login_protection.login_max_attempts' => 1,
            'login_protection.login_lockout_seconds' => 99999,
            'login_protection.two_factor_challenge_lifetime' => 60,
            'retention.audit_retention_days' => 99999,
        ]);

        $settings = app(AdminApiAuthSettings::class);

        $this->assertSame(AdminApiAuthSettings::REQUESTS_PER_MINUTE_MAX, $settings->sessionRequestsPerMinute());
        $this->assertSame(10, $settings->unauthenticatedRequestsPerMinute());
        $this->assertSame(3, $settings->loginMaxAttempts());
        $this->assertSame(3600, $settings->loginLockoutSeconds());
        $this->assertSame(15, $settings->twoFactorChallengeLifetime());
        $this->assertSame(3650, $settings->auditRetentionDays());
    }

    /**
     * Test an audit retention of 0 keeps the history forever.
     */
    public function test_an_audit_retention_of_zero_keeps_the_history(): void
    {
        $this->configure(['retention.audit_retention_days' => 0]);

        $this->assertNull(app(AdminApiAuthSettings::class)->auditRetentionDays());
    }

    /**
     * Test the audit retention falls back to the environment value when the field is empty.
     */
    public function test_the_audit_retention_falls_back_to_the_package_config(): void
    {
        config(['bagistoapi.audit.retention_days' => 45]);

        $this->assertSame(45, app(AdminApiAuthSettings::class)->auditRetentionDays());
    }

    // ========================================================================
    // Login Protection
    // ========================================================================

    /**
     * Test the login lockout follows the configured attempts and lockout time.
     */
    public function test_the_login_lockout_follows_the_configured_limits(): void
    {
        $this->configure([
            'login_protection.login_max_attempts' => 3,
            'login_protection.login_lockout_seconds' => 120,
        ]);

        $admin = $this->createAdmin();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->login($admin->email, 'wrong-'.$attempt)->assertStatus(401);
        }

        $response = $this->login($admin->email, $this->adminPassword);

        $response->assertStatus(429)->assertJsonPath('error', 'too_many_login_attempts');
        $this->assertGreaterThan(60, (int) $response->headers->get('Retry-After'));

        $this->travel(61)->seconds();

        $this->login($admin->email, $this->adminPassword)->assertStatus(429);

        $this->travel(60)->seconds();

        $this->login($admin->email, $this->adminPassword)->assertOk();
    }

    /**
     * Test a two-factor challenge expires after the configured lifetime.
     */
    public function test_a_two_factor_challenge_lasts_the_configured_lifetime(): void
    {
        if (! Schema::hasColumn('admins', 'two_factor_enabled')) {
            $this->markTestSkipped('Two-factor authentication needs a core with the admin two-factor columns.');
        }

        $this->configure(['login_protection.two_factor_challenge_lifetime' => 10]);

        $admin = $this->createAdmin();

        $admin->update([
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_enabled' => true,
            'two_factor_verified_at' => now(),
        ]);

        $this->freezeSecond();

        $this->login($admin->email, $this->adminPassword)->assertStatus(202);

        $challenge = AdminApiTwoFactorChallenge::query()->where('admin_id', $admin->id)->latest('id')->firstOrFail();

        $this->assertSame(now()->addMinutes(10)->timestamp, $challenge->expires_at->timestamp);
    }

    /**
     * Test password requests are refused once the configured limit is reached.
     */
    public function test_password_requests_follow_the_configured_limit(): void
    {
        Notification::fake();
        Mail::fake();

        $this->configure(['login_protection.password_requests_per_minute' => 1]);

        $email = 'nobody-'.uniqid().'@example.com';

        $this->postJson('/api/admin/auth/forgot-password', ['email' => $email])->assertOk();

        $this->postJson('/api/admin/auth/forgot-password', ['email' => $email])
            ->assertStatus(429)
            ->assertJsonPath('error', 'too_many_password_requests');
    }

    // ========================================================================
    // Audit Retention
    // ========================================================================

    /**
     * Test the audit prune deletes rows older than the configured retention.
     */
    public function test_the_audit_prune_uses_the_configured_retention(): void
    {
        $this->configure(['retention.audit_retention_days' => 10]);

        $old = $this->createAudit(now()->subDays(11));
        $recent = $this->createAudit(now()->subDays(9));

        $this->artisan('bagisto-api:prune-audits')->assertSuccessful();

        $this->assertDatabaseMissing('admin_api_audits', ['id' => $old->id]);
        $this->assertDatabaseHas('admin_api_audits', ['id' => $recent->id]);
    }

    /**
     * Test the audit prune keeps everything when no retention is configured.
     */
    public function test_the_audit_prune_keeps_everything_without_a_retention(): void
    {
        config(['bagistoapi.audit.retention_days' => null]);

        $old = $this->createAudit(now()->subYears(5));

        $this->artisan('bagisto-api:prune-audits')->assertSuccessful();

        $this->assertDatabaseHas('admin_api_audits', ['id' => $old->id]);
    }

    /**
     * Test the audit prune is scheduled daily.
     */
    public function test_the_audit_prune_is_scheduled(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains((string) $event->command, 'bagisto-api:prune-audits'));

        $this->assertCount(1, $events);
        $this->assertSame('0 0 * * *', $events->first()->expression);
    }

    // ========================================================================
    // Security Headers
    // ========================================================================

    /**
     * Test the configured Content-Security-Policy is sent.
     */
    public function test_the_configured_content_security_policy_is_sent(): void
    {
        config(['api-platform.security_headers.csp_header' => "default-src 'none'"]);

        $this->getJson(self::PROFILE)
            ->assertHeader('Content-Security-Policy', "default-src 'none'");
    }

    /**
     * Test no security headers are sent when they are switched off.
     */
    public function test_security_headers_can_be_switched_off(): void
    {
        config(['api-platform.security_headers.enabled' => false]);

        $this->getJson(self::PROFILE)
            ->assertHeaderMissing('Content-Security-Policy')
            ->assertHeaderMissing('Permissions-Policy');
    }

    /**
     * Save admin authentication settings, keyed below `api.admin_auth`.
     *
     * @param  array<string, int|string>  $values
     */
    protected function configure(array $values): void
    {
        foreach ($values as $field => $value) {
            $code = AdminApiAuthSettings::CONFIG_PREFIX.'.'.$field;

            CoreConfig::query()->where('code', $code)->delete();

            CoreConfig::query()->create([
                'code' => $code,
                'value' => (string) $value,
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    /**
     * Send a REST login.
     */
    protected function login(string $email, string $password): TestResponse
    {
        return $this->postJson(self::LOGIN, [
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * Create an audit row written at the given time.
     */
    protected function createAudit(mixed $createdAt): AdminApiAudit
    {
        $audit = new AdminApiAudit;

        $audit->forceFill([
            'history_id' => (string) str()->uuid(),
            'event' => 'updated',
            'method' => 'PUT',
            'created_at' => $createdAt,
        ])->save();

        return $audit;
    }
}
