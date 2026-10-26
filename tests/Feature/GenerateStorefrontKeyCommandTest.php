<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Webkul\BagistoApi\Models\StorefrontKey;
use Webkul\BagistoApi\Tests\BagistoApiTestCase;

class GenerateStorefrontKeyCommandTest extends BagistoApiTestCase
{
    /**
     * Test an empty rate limit keeps the default instead of failing on the not-null column.
     */
    public function test_an_empty_rate_limit_keeps_the_default(): void
    {
        $name = 'empty-limit-'.uniqid();

        $this->artisan('bagisto-api:generate-key', ['--name' => $name, '--rate-limit' => ''])->assertSuccessful();

        expect(StorefrontKey::where('name', $name)->value('rate_limit'))->toBe(100);
    }

    /**
     * Test a rate limit is stored, and one above the maximum is capped.
     */
    public function test_a_rate_limit_is_stored_and_capped(): void
    {
        $name = 'limit-'.uniqid();
        $capped = 'capped-'.uniqid();

        $this->artisan('bagisto-api:generate-key', ['--name' => $name, '--rate-limit' => '250'])->assertSuccessful();
        $this->artisan('bagisto-api:generate-key', ['--name' => $capped, '--rate-limit' => '999999'])->assertSuccessful();

        expect(StorefrontKey::where('name', $name)->value('rate_limit'))->toBe(250);
        expect(StorefrontKey::where('name', $capped)->value('rate_limit'))->toBe(5000);
    }
}
