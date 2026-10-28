<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class TwoFactorTest extends AdminApiTestCase
{
    public function test_setup_enable_and_disable(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();

        $setup = $this->adminGraphQL('{ setupAdminTwoFactor { enabled qrCodeUrl qrCodeSvg } }', [], $admin);

        $setup->assertOk();
        $this->assertNull($setup->json('errors'));
        $this->assertStringStartsWith('otpauth://', $setup->json('data.setupAdminTwoFactor.qrCodeUrl'));

        $code = app(Google2FA::class)->getCurrentOtp(decrypt($admin->fresh()->two_factor_secret));

        $enable = $this->adminGraphQL(<<<'GQL'
            mutation ($input: enableAdminTwoFactorInput!) {
              enableAdminTwoFactor(input: $input) {
                adminTwoFactor {
                  enabled
                  backupCodes
                  message
                }
              }
            }
        GQL, ['input' => ['id' => '/api/admin/account/two-factor', 'code' => $code]], $admin);

        $this->assertNull($enable->json('errors'));
        $this->assertTrue($enable->json('data.enableAdminTwoFactor.adminTwoFactor.enabled'));
        $this->assertCount(8, $enable->json('data.enableAdminTwoFactor.adminTwoFactor.backupCodes'));

        $disable = $this->adminGraphQL(<<<'GQL'
            mutation ($input: disableAdminTwoFactorInput!) {
              disableAdminTwoFactor(input: $input) {
                adminTwoFactor {
                  enabled
                  message
                }
              }
            }
        GQL, ['input' => ['id' => '/api/admin/account/two-factor', 'code' => $code]], $admin);

        $this->assertNull($disable->json('errors'));
        $this->assertFalse($disable->json('data.disableAdminTwoFactor.adminTwoFactor.enabled'));
        $this->assertFalse((bool) $admin->fresh()->two_factor_enabled);
    }

    public function test_enable_rejects_invalid_code(): void
    {
        $admin = $this->createAdmin();

        $this->adminGraphQL('{ setupAdminTwoFactor { enabled } }', [], $admin);

        $response = $this->adminGraphQL(<<<'GQL'
            mutation ($input: enableAdminTwoFactorInput!) {
              enableAdminTwoFactor(input: $input) {
                adminTwoFactor {
                  enabled
                }
              }
            }
        GQL, ['input' => ['id' => '/api/admin/account/two-factor', 'code' => '000000']], $admin);

        $this->assertNotEmpty($response->json('errors'));
    }

    public function test_setup_requires_authentication(): void
    {
        $this->assertNotEmpty($this->adminGraphQL('{ setupAdminTwoFactor { enabled } }')->json('errors'));
    }
}
