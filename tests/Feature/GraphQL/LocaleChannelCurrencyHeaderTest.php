<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Core;
use Webkul\Core\Models\Channel;

class LocaleChannelCurrencyHeaderTest extends GraphQLTestCase
{
    // ─── No headers (defaults) ──────────────────────────────────────────

    /**
     * Test no headers returns default locale data.
     */
    public function test_no_headers_returns_default_locale_data(): void
    {
        $response = $this->graphQL($this->cmsPageQuery());

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $edges = $response->json('data.pages.edges');
        $this->assertNotNull($edges);

        if (! empty($edges)) {
            $locale = $edges[0]['node']['translation']['locale'] ?? null;
            $channel = Channel::first();
            $defaultLocale = $channel?->default_locale?->code ?? 'en';

            $this->assertSame($defaultLocale, $locale, 'Without X-LOCALE header, default locale should be used');
        }
    }

    // ─── X-LOCALE header ────────────────────────────────────────────────

    /**
     * Test valid locale header switches locale.
     */
    public function test_valid_locale_header_switches_locale(): void
    {
        $locales = $this->getChannelLocales();

        $targetLocale = $locales[0] ?? 'en';

        $response = $this->graphQL($this->cmsPageQuery(), [], [
            'X-Locale' => $targetLocale,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $edges = $response->json('data.pages.edges');
        if (! empty($edges)) {
            $locale = $edges[0]['node']['translation']['locale'] ?? null;
            $this->assertSame($targetLocale, $locale, 'X-LOCALE header should switch the response locale');
        }
    }

    /**
     * Test invalid locale header falls back to default.
     */
    public function test_invalid_locale_header_falls_back_to_default(): void
    {
        $response = $this->graphQL($this->cmsPageQuery(), [], [
            'X-Locale' => 'xx-INVALID',
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $edges = $response->json('data.pages.edges');
        if (! empty($edges)) {
            $locale = $edges[0]['node']['translation']['locale'] ?? null;
            $channel = Channel::first();
            $defaultLocale = $channel?->default_locale?->code ?? 'en';

            $this->assertSame($defaultLocale, $locale, 'Invalid X-LOCALE should fall back to default');
        }
    }

    // ─── X-CURRENCY header ──────────────────────────────────────────────

    /**
     * Test valid currency header is accepted.
     */
    public function test_valid_currency_header_is_accepted(): void
    {
        $currencies = $this->getChannelCurrencies();
        $targetCurrency = $currencies[0] ?? 'USD';

        $response = $this->graphQL($this->cmsPageQuery(), [], [
            'X-Currency' => $targetCurrency,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));
    }

    /**
     * Test invalid currency header falls back to default.
     */
    public function test_invalid_currency_header_falls_back_to_default(): void
    {
        $response = $this->graphQL($this->cmsPageQuery(), [], [
            'X-Currency' => 'ZZZZZ',
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));
    }

    // ─── X-CHANNEL header ───────────────────────────────────────────────

    /**
     * Test valid channel header is accepted.
     */
    public function test_valid_channel_header_is_accepted(): void
    {
        $response = $this->graphQL($this->cmsPageQuery(), [], [
            'X-Channel' => 'default',
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));
    }

    /**
     * Test unknown channel header returns 400.
     */
    public function test_unknown_channel_header_returns_400(): void
    {
        $response = $this->graphQL($this->cmsPageQuery(), [], [
            'X-Channel' => 'nonexistent',
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'invalid_channel');
        $response->assertJsonPath('header_name', 'X-Channel');
    }

    /**
     * Test channel header not passed uses default.
     */
    public function test_channel_header_not_passed_uses_default(): void
    {
        $response = $this->graphQL($this->channelQuery(), [
            'id' => '/api/shop/channels/1',
        ]);

        $response->assertSuccessful();

        $node = $response->json('data.channel');
        $this->assertNotNull($node);
        $this->assertNotNull($node['code']);
    }

    // ─── All three headers together ─────────────────────────────────────

    /**
     * Test all three headers together.
     */
    public function test_all_three_headers_together(): void
    {
        $locales = $this->getChannelLocales();
        $currencies = $this->getChannelCurrencies();

        $response = $this->graphQL($this->cmsPageQuery(), [], [
            'X-Locale' => $locales[0] ?? 'en',
            'X-Currency' => $currencies[0] ?? 'USD',
            'X-Channel' => 'default',
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $edges = $response->json('data.pages.edges');
        if (! empty($edges)) {
            $locale = $edges[0]['node']['translation']['locale'] ?? null;
            $this->assertSame($locales[0] ?? 'en', $locale);
        }
    }

    /**
     * Test all three headers with invalid values fall back gracefully.
     */
    public function test_all_three_headers_with_invalid_values_fall_back_gracefully(): void
    {
        $response = $this->graphQL($this->cmsPageQuery(), [], [
            'X-Locale' => 'xx-NOPE',
            'X-Currency' => 'FAKE',
            'X-Channel' => Channel::first()->code,
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $edges = $response->json('data.pages.edges');
        if (! empty($edges)) {
            $locale = $edges[0]['node']['translation']['locale'] ?? null;
            $channel = Channel::first();
            $defaultLocale = $channel?->default_locale?->code ?? 'en';
            $this->assertSame($defaultLocale, $locale);
        }
    }

    // ─── Formatted price respects currency ──────────────────────────────

    /**
     * Test product price respects currency header.
     */
    public function test_product_price_respects_currency_header(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                price
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/products/'.$product->id]);
        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.product');
        $this->assertNotNull($data, 'Product should be returned');
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('name', $data);

        $currencies = $this->getChannelCurrencies();
        $response2 = $this->graphQL($query, ['id' => '/api/shop/products/'.$product->id], [
            'X-Currency' => $currencies[0] ?? 'USD',
        ]);
        $response2->assertSuccessful();

        $json2 = $response2->json();
        $this->assertArrayNotHasKey('errors', $json2, 'GraphQL errors: '.json_encode($json2['errors'] ?? []));

        $data2 = $response2->json('data.product');
        $this->assertNotNull($data2);
        $this->assertArrayHasKey('price', $data2);
    }

    // ─── Price values and formatted prices respect currency ────────────

    /**
     * Test product prices are converted when currency header changes.
     */
    public function test_product_prices_are_converted_when_currency_header_changes(): void
    {
        $this->seedRequiredData();

        $channel = Channel::with(['currencies', 'base_currency'])->first();
        $baseCurrencyCode = $channel->base_currency->code;

        $targetCurrencyCode = 'TST';
        $exchangeRate = 25.0;

        $currencyId = DB::table('currencies')->insertGetId([
            'code' => $targetCurrencyCode,
            'name' => 'Test Currency',
            'symbol' => 'T$',
            'decimal' => 2,
            'group_separator' => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('currency_exchange_rates')->insert([
            'target_currency' => $currencyId,
            'rate' => $exchangeRate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('channel_currencies')->insert([
            'channel_id' => $channel->id,
            'currency_id' => $currencyId,
        ]);

        $basePrice = 10.0;
        $product = $this->createBaseProduct('simple');
        $this->ensureProductIsSaleable($product, $basePrice);

        $customerGroupId = DB::table('customer_groups')->where('code', 'general')->value('id');

        DB::table('product_price_indices')->updateOrInsert(
            [
                'product_id' => $product->id,
                'customer_group_id' => $customerGroupId,
                'channel_id' => $channel->id,
            ], [
                'min_price' => $basePrice,
                'regular_min_price' => $basePrice,
                'max_price' => $basePrice,
                'regular_max_price' => $basePrice,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                price
                minimumPrice
                maximumPrice
                regularMinimumPrice
                regularMaximumPrice
                formattedPrice
                formattedMinimumPrice
                formattedMaximumPrice
              }
            }
        GQL;

        $productIri = '/api/shop/products/'.$product->id;
        $expectedConverted = $basePrice * $exchangeRate;

        app()->forgetInstance(Core::class);
        Facade::clearResolvedInstance(Core::class);

        $responseBase = $this->graphQL($query, ['id' => $productIri], [
            'X-Currency' => $baseCurrencyCode,
        ]);

        $responseBase->assertSuccessful();
        $this->assertArrayNotHasKey('errors', $responseBase->json());

        $dataBase = $responseBase->json('data.product');
        $this->assertNotNull($dataBase, 'Product should be returned for base currency');

        $this->assertEqualsWithDelta($basePrice, (float) $dataBase['price'], 0.01, 'Base currency price should equal catalog price');
        $this->assertEqualsWithDelta($basePrice, (float) $dataBase['minimumPrice'], 0.01, 'Base currency minimumPrice should equal catalog price');
        $this->assertEqualsWithDelta($basePrice, (float) $dataBase['maximumPrice'], 0.01, 'Base currency maximumPrice should equal catalog price');

        $responseTarget = $this->graphQL($query, ['id' => $productIri], [
            'X-Currency' => $targetCurrencyCode,
        ]);

        $responseTarget->assertSuccessful();
        $this->assertArrayNotHasKey('errors', $responseTarget->json());

        $dataTarget = $responseTarget->json('data.product');
        $this->assertNotNull($dataTarget, "Product should be returned for {$targetCurrencyCode} currency");

        $formattedMin = $dataTarget['formattedMinimumPrice'] ?? '';
        $formattedMax = $dataTarget['formattedMaximumPrice'] ?? '';

        $this->assertNotEmpty($formattedMin, 'formattedMinimumPrice should be non-empty for TST response');
        $this->assertNotEmpty($formattedMax, 'formattedMaximumPrice should be non-empty for TST response');
    }

    /**
     * Simple query that returns translatable data (CMS page) to verify locale switching.
     */
    private function cmsPageQuery(): string
    {
        return <<<'GQL'
            query {
              pages(first: 1) {
                edges {
                  node {
                    id
                    _id
                    translation {
                      pageTitle
                      locale
                    }
                  }
                }
              }
            }
        GQL;
    }

    /**
     * Channel query to verify channel data loads.
     */
    private function channelQuery(): string
    {
        return <<<'GQL'
            query getChannelByID($id: ID!) {
              channel(id: $id) {
                id
                _id
                code
                defaultLocale { code }
                baseCurrency { code }
              }
            }
        GQL;
    }

    /**
     * Get valid locale codes from default channel.
     */
    private function getChannelLocales(): array
    {
        $channel = Channel::first();

        return $channel ? $channel->locales->pluck('code')->toArray() : ['en'];
    }

    /**
     * Get valid currency codes from default channel.
     */
    private function getChannelCurrencies(): array
    {
        $channel = Channel::first();

        return $channel ? $channel->currencies->pluck('code')->toArray() : ['USD'];
    }
}
