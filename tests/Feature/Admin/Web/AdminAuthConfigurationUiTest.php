<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\Web;

use Tests\TestCase;
use Webkul\Core\Models\CoreConfig;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class AdminAuthConfigurationUiTest extends TestCase
{
    /**
     * Test the configuration screen lists the login protection and retention groups.
     */
    public function test_the_configuration_screen_renders_the_new_groups(): void
    {
        $this->actingAdmin();

        $this->get(route('admin.configuration.index', ['api', 'admin_auth']))
            ->assertOk()
            ->assertSee(trans('bagistoapi::app.admin.auth.configuration.login-protection.title'))
            ->assertSee(trans('bagistoapi::app.admin.auth.configuration.retention.title'));
    }

    /**
     * Test a value inside its range is saved.
     */
    public function test_a_value_inside_its_range_is_saved(): void
    {
        $this->actingAdmin();

        $this->post(route('admin.configuration.store', ['api', 'admin_auth']), $this->form([
            'login_protection' => $this->loginProtection(['login_max_attempts' => 10]),
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(
            '10',
            CoreConfig::query()->where('code', 'api.admin_auth.login_protection.login_max_attempts')->value('value'),
        );
    }

    /**
     * Test values outside their range are refused.
     */
    public function test_values_outside_their_range_are_refused(): void
    {
        $this->actingAdmin();

        $this->post(route('admin.configuration.store', ['api', 'admin_auth']), $this->form([
            'rate_limits' => [
                'session_requests_per_minute' => 100001,
                'api_token_requests_per_minute' => 60,
                'unauthenticated_requests_per_minute' => 60,
            ],
            'login_protection' => $this->loginProtection(['login_max_attempts' => 99]),
            'retention' => ['session_prune_days' => 30, 'audit_retention_days' => 5000],
        ]))->assertSessionHasErrors([
            'api.admin_auth.rate_limits.session_requests_per_minute',
            'api.admin_auth.login_protection.login_max_attempts',
            'api.admin_auth.retention.audit_retention_days',
        ]);

        $this->assertNull(CoreConfig::query()->where('code', 'api.admin_auth.login_protection.login_max_attempts')->value('value'));
    }

    /**
     * Test the audit retention may be left empty.
     */
    public function test_the_audit_retention_may_be_left_empty(): void
    {
        $this->actingAdmin();

        $this->post(route('admin.configuration.store', ['api', 'admin_auth']), $this->form([
            'retention' => ['session_prune_days' => 30, 'audit_retention_days' => ''],
        ]))->assertSessionHasNoErrors();
    }

    /**
     * Sign in as a new all-access admin.
     */
    protected function actingAdmin(): Admin
    {
        $admin = Admin::factory()->create([
            'role_id' => Role::query()->where('permission_type', 'all')->value('id'),
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Every login protection field at its default, with the given values on top.
     *
     * @param  array<string, int>  $values
     * @return array<string, int>
     */
    protected function loginProtection(array $values): array
    {
        return array_merge([
            'login_max_attempts' => 5,
            'login_lockout_seconds' => 60,
            'two_factor_challenge_lifetime' => 5,
            'two_factor_max_failures' => 5,
            'password_requests_per_minute' => 5,
        ], $values);
    }

    /**
     * Build a configuration form body for the admin authentication groups.
     *
     * @param  array<string, array<string, mixed>>  $groups
     */
    protected function form(array $groups): array
    {
        return [
            'channel' => core()->getDefaultChannelCode(),
            'locale' => app()->getLocale(),
            'api' => ['admin_auth' => $groups],
        ];
    }
}
