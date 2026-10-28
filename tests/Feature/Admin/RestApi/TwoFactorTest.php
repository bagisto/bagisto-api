<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;

class TwoFactorTest extends AdminApiTestCase
{
    private function currentCode(Admin $admin): string
    {
        return app(Google2FA::class)->getCurrentOtp(decrypt($admin->fresh()->two_factor_secret));
    }

    public function test_setup_returns_qr_code_and_stores_secret(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/account/two-factor');

        $response->assertOk();
        $this->assertStringStartsWith('otpauth://', $response->json('0.qrCodeUrl'));
        $this->assertNotEmpty($response->json('0.qrCodeSvg'));
        $this->assertNotNull($admin->fresh()->two_factor_secret);
    }

    public function test_enable_with_valid_code_returns_backup_codes(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/account/two-factor')->assertOk();

        $response = $this->adminPost($admin, '/api/admin/account/two-factor/enable', ['code' => $this->currentCode($admin)]);

        $response->assertOk();
        $this->assertTrue($response->json('enabled'));
        $this->assertCount(8, $response->json('backupCodes'));
        $this->assertTrue((bool) $admin->fresh()->two_factor_enabled);
    }

    public function test_enable_rejects_invalid_code(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/account/two-factor')->assertOk();

        $response = $this->adminPost($admin, '/api/admin/account/two-factor/enable', ['code' => '000000']);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse((bool) $admin->fresh()->two_factor_enabled);
    }

    public function test_enable_requires_setup_first(): void
    {
        $response = $this->adminPost($this->createAdmin(), '/api/admin/account/two-factor/enable', ['code' => '123456']);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_setup_is_refused_once_enabled(): void
    {
        $admin = $this->createAdmin(['two_factor_enabled' => true, 'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP')]);

        $this->assertSame(422, $this->adminGet($admin, '/api/admin/account/two-factor')->getStatusCode());
    }

    public function test_disable_requires_a_valid_code_while_enabled(): void
    {
        $admin = $this->createAdmin(['two_factor_enabled' => true, 'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey())]);

        $this->assertSame(422, $this->adminPost($admin, '/api/admin/account/two-factor/disable', [])->getStatusCode());
        $this->assertTrue((bool) $admin->fresh()->two_factor_enabled);

        $response = $this->adminPost($admin, '/api/admin/account/two-factor/disable', ['code' => $this->currentCode($admin)]);

        $response->assertOk();
        $this->assertFalse($response->json('enabled'));
        $this->assertFalse((bool) $admin->fresh()->two_factor_enabled);
        $this->assertNull($admin->fresh()->two_factor_secret);
    }

    public function test_requires_authentication(): void
    {
        $this->assertSame(401, $this->publicGet('/api/admin/account/two-factor')->getStatusCode());
        $this->assertSame(401, $this->publicPost('/api/admin/account/two-factor/enable', ['code' => '123456'])->getStatusCode());
    }
}
