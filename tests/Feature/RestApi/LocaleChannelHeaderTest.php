<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;

class LocaleChannelHeaderTest extends RestApiTestCase
{
    /**
     * Test unknown channel header returns 400.
     */
    public function test_unknown_channel_header_returns_400(): void
    {
        $response = $this->getJson('/api/shop/channels', array_merge($this->storefrontHeaders(), [
            'X-Channel' => 'no-such-channel-code',
        ]));

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'invalid_channel');
        $response->assertJsonPath('header_name', 'X-Channel');

        $this->assertStringContainsString('no-such-channel-code', (string) $response->json('message'));
    }

    /**
     * Test known channel header is applied to core.
     */
    public function test_known_channel_header_is_applied_to_core(): void
    {
        $channel = Channel::factory()->create();

        $response = $this->getJson('/api/shop/channels', array_merge($this->storefrontHeaders(), [
            'X-Channel' => $channel->code,
        ]));

        $response->assertOk();

        $this->assertSame($channel->code, core()->getCurrentChannel()->code);
    }

    /**
     * Test locale header is validated against the requested channel.
     */
    public function test_locale_header_is_validated_against_the_requested_channel(): void
    {
        $channel = Channel::factory()->create();
        $channelLocales = $channel->locales()->pluck('code')->toArray();

        $response = $this->getJson('/api/shop/channels', array_merge($this->storefrontHeaders(), [
            'X-Channel' => $channel->code,
            'X-Locale' => $channelLocales[0],
        ]));

        $response->assertOk();

        $this->assertSame($channelLocales[0], app()->getLocale());
    }

    /**
     * Test missing channel header keeps the hostname channel.
     */
    public function test_missing_channel_header_keeps_the_hostname_channel(): void
    {
        $expected = core()->getCurrentChannel()?->code;

        $response = $this->publicGet('/api/shop/channels');

        $response->assertOk();

        $this->assertSame($expected, core()->getCurrentChannel()?->code);
    }
}
