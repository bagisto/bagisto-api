<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class FeatureStatusTest extends RestApiTestCase
{
    private function setFlag(string $code, ?string $value, bool $localeScoped = true): void
    {
        DB::table('core_config')->where('code', $code)->delete();

        if ($value !== null) {
            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => $localeScoped ? core()->getRequestedLocaleCode() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    public function test_gdpr_status_returns_the_current_channel(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/gdpr-status');

        $response->assertOk();

        $body = $response->json();

        expect(count($body))->toBe(1);
        expect($body[0])->toHaveKeys(['id', 'channel', 'enabled']);
        expect($body[0]['channel'])->toBe(core()->getCurrentChannel()->code);
    }

    public function test_gdpr_status_is_enabled_when_the_store_enables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('general.gdpr.settings.enabled', '1');

        $response = $this->publicGet('/api/shop/gdpr-status');

        $response->assertOk();
        expect($response->json('0.enabled'))->toBeTrue();
    }

    public function test_gdpr_status_is_disabled_when_the_store_disables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('general.gdpr.settings.enabled', null);

        $response = $this->publicGet('/api/shop/gdpr-status');

        $response->assertOk();
        expect($response->json('0.enabled'))->toBeFalse();
    }

    public function test_eu_withdrawal_status_returns_the_current_channel(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/eu-withdrawal-status');

        $response->assertOk();

        $body = $response->json();

        expect(count($body))->toBe(1);
        expect($body[0])->toHaveKeys(['id', 'channel', 'enabled']);
        expect($body[0]['channel'])->toBe(core()->getCurrentChannel()->code);
    }

    public function test_eu_withdrawal_status_is_enabled_when_the_store_enables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('sales.eu_withdrawal.general.enabled', '1', false);

        $response = $this->publicGet('/api/shop/eu-withdrawal-status');

        $response->assertOk();
        expect($response->json('0.enabled'))->toBeTrue();
    }

    public function test_eu_withdrawal_status_is_disabled_when_the_store_disables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('sales.eu_withdrawal.general.enabled', null, false);

        $response = $this->publicGet('/api/shop/eu-withdrawal-status');

        $response->assertOk();
        expect($response->json('0.enabled'))->toBeFalse();
    }
}
