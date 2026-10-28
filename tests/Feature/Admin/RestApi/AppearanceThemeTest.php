<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class AppearanceThemeTest extends AdminApiTestCase
{
    /**
     * Test list returns the theme gallery.
     */
    public function test_list_returns_the_theme_gallery(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes');

        $response->assertOk();

        $body = $response->json();

        expect($body)->toBeArray();
        expect(count($body))->toBeGreaterThan(0);
        expect($body[0])->toHaveKeys(['code', 'name', 'isInstalled', 'status', 'activeOn']);
    }

    /**
     * Test list requires authentication.
     */
    public function test_list_requires_authentication(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/admin/appearance/themes');

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    /**
     * Test list requires permission.
     */
    public function test_list_requires_permission(): void
    {
        [$admin, $token] = $this->adminWithRole('custom', ['sales.orders']);

        $response = $this->adminGet($admin, '/api/admin/appearance/themes', $token);

        $response->assertForbidden();
    }

    /**
     * Test get returns a single theme.
     */
    public function test_get_returns_a_single_theme(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/default');

        $response->assertOk();

        expect($response->json('code'))->toBe('default');
        expect($response->json('isInstalled'))->toBeTrue();
    }

    /**
     * Test get unknown theme returns 404.
     */
    public function test_get_unknown_theme_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/no-such-theme');

        $response->assertNotFound();
    }

    /**
     * Test impact reports nothing for the active theme.
     */
    public function test_impact_reports_nothing_for_the_active_theme(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/default/impact?channel_ids[]='.$channelId);

        $response->assertOk();

        expect($response->json('code'))->toBe('default');
        expect($response->json('impact'))->toBe([]);
    }

    /**
     * Test impact without channels returns 422.
     */
    public function test_impact_without_channels_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/default/impact');

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test impact with unknown channel returns 422.
     */
    public function test_impact_with_unknown_channel_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/default/impact?channel_ids[]=999999');

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test activate points the channel at the theme.
     */
    public function test_activate_points_the_channel_at_the_theme(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        \DB::table('channels')->where('id', $channelId)->update(['theme' => null]);

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/activate', [
            'channelIds' => [$channelId],
        ]);

        $response->assertSuccessful();

        expect($response->json('code'))->toBe('default');
        expect(\DB::table('channels')->where('id', $channelId)->value('theme'))->toBe('default');
    }

    /**
     * Test activate unknown theme returns 404.
     */
    public function test_activate_unknown_theme_returns_404(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/no-such-theme/activate', [
            'channelIds' => [$channelId],
        ]);

        $response->assertNotFound();
    }

    /**
     * Test activate without channels returns 422.
     */
    public function test_activate_without_channels_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/activate', []);

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test activate requires the activate permission.
     */
    public function test_activate_requires_the_activate_permission(): void
    {
        [$admin, $token] = $this->adminWithRole('custom', ['appearance.themes']);
        $channelId = $this->channelId();

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/activate', [
            'channelIds' => [$channelId],
        ], $token);

        $response->assertForbidden();
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'Appearance sections');
    }

    /**
     * Channel id.
     */
    private function channelId(): int
    {
        $this->seedRequiredData();

        return (int) \DB::table('channels')->orderBy('id')->value('id');
    }

    /**
     * Admin with role.
     */
    private function adminWithRole(string $permissionType, array $permissions = []): array
    {
        $this->seedRequiredData();

        $role = Role::create([
            'name' => 'appearance-role-'.uniqid(),
            'description' => 'test',
            'permission_type' => $permissionType,
            'permissions' => $permissions,
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);

        return [$admin, $this->adminAccessToken($admin)];
    }
}
