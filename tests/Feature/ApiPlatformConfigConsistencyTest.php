<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Webkul\BagistoApi\Http\Middleware\SetLocaleChannel;
use Webkul\BagistoApi\Tests\BagistoApiTestCase;

class ApiPlatformConfigConsistencyTest extends BagistoApiTestCase
{
    private function packageConfig(string $file): array
    {
        return require dirname(__DIR__, 2).'/config/'.$file;
    }

    public function test_vendor_config_carries_the_same_middleware_as_the_package_config(): void
    {
        $package = $this->packageConfig('api-platform.php');
        $vendor = $this->packageConfig('api-platform-vendor.php');

        $this->assertSame($package['routes']['middleware'], $vendor['routes']['middleware']);
        $this->assertSame($package['graphql']['middleware'], $vendor['graphql']['middleware']);
    }

    public function test_every_config_applies_the_locale_channel_headers(): void
    {
        foreach (['api-platform.php', 'api-platform-vendor.php'] as $file) {
            $config = $this->packageConfig($file);

            $this->assertContains(SetLocaleChannel::class, $config['routes']['middleware'], "{$file} routes");
            $this->assertContains(SetLocaleChannel::class, $config['graphql']['middleware'], "{$file} graphql");
        }

        $this->assertContains(SetLocaleChannel::class, config('api-platform.routes.middleware'));
        $this->assertContains(SetLocaleChannel::class, config('api-platform.graphql.middleware'));
    }
}
