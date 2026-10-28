<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class FeatureStatusTest extends GraphQLTestCase
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

        $response = $this->graphQL('query { gdprStatus { channel enabled } }');

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.gdprStatus.channel'))->toBe(core()->getCurrentChannel()->code);
    }

    public function test_gdpr_status_follows_the_store_setting(): void
    {
        $this->seedRequiredData();
        $this->setFlag('general.gdpr.settings.enabled', '1');

        $response = $this->graphQL('query { gdprStatus { enabled } }');

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.gdprStatus.enabled'))->toBeTrue();
    }

    public function test_eu_withdrawal_status_returns_the_current_channel(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL('query { euWithdrawalStatus { channel enabled } }');

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.euWithdrawalStatus.channel'))->toBe(core()->getCurrentChannel()->code);
    }

    public function test_eu_withdrawal_status_follows_the_store_setting(): void
    {
        $this->seedRequiredData();
        $this->setFlag('sales.eu_withdrawal.general.enabled', '1', false);

        $response = $this->graphQL('query { euWithdrawalStatus { enabled } }');

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.euWithdrawalStatus.enabled'))->toBeTrue();
    }
}
