<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\MagicAI\Facades\MagicAI;

class MagicAiTest extends AdminApiTestCase
{
    private function setConfig(string $code, ?string $value): void
    {
        DB::table('core_config')->where('code', $code)->delete();

        if ($value !== null) {
            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    private function enable(string $feature): void
    {
        $this->setConfig('magic_ai.general.settings.enabled', '1');
        $this->setConfig("magic_ai.admin_features.{$feature}.enabled", '1');
    }

    public function test_content_returns_generated_text(): void
    {
        $this->enable('text_generation');

        MagicAI::shouldReceive('generateContent')
            ->once()
            ->with('Describe a red t-shirt', 'gpt-4o-mini')
            ->andReturn('A soft red cotton tee.');

        $response = $this->adminPost($this->createAdmin(), '/api/admin/magic-ai/content', [
            'prompt' => 'Describe a red t-shirt',
            'model' => 'gpt-4o-mini',
        ]);

        $response->assertOk();
        $this->assertSame('A soft red cotton tee.', $response->json('content'));
    }

    public function test_content_requires_prompt(): void
    {
        $this->enable('text_generation');

        $response = $this->adminPost($this->createAdmin(), '/api/admin/magic-ai/content', []);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_content_is_refused_when_text_generation_is_disabled(): void
    {
        $this->setConfig('magic_ai.general.settings.enabled', '1');
        $this->setConfig('magic_ai.admin_features.text_generation.enabled', '0');

        $response = $this->adminPost($this->createAdmin(), '/api/admin/magic-ai/content', ['prompt' => 'x']);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_content_surfaces_provider_errors_as_500(): void
    {
        $this->enable('text_generation');

        MagicAI::shouldReceive('generateContent')->andThrow(new \Exception('Invalid API key'));

        $response = $this->adminPost($this->createAdmin(), '/api/admin/magic-ai/content', ['prompt' => 'x']);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Invalid API key', $response->json('message'));
    }

    public function test_image_returns_generated_images(): void
    {
        $this->enable('image_generation');

        MagicAI::shouldReceive('generateImage')
            ->once()
            ->with('A red t-shirt', ['n' => 2, 'size' => '1:1'], null)
            ->andReturn([['url' => 'data:image/png;base64,AAA'], ['url' => 'data:image/png;base64,BBB']]);

        $response = $this->adminPost($this->createAdmin(), '/api/admin/magic-ai/image', [
            'prompt' => 'A red t-shirt',
            'n' => 2,
            'size' => '1:1',
        ]);

        $response->assertOk();
        $this->assertCount(2, $response->json('images'));
    }

    public function test_image_rejects_unknown_size(): void
    {
        $this->enable('image_generation');

        $response = $this->adminPost($this->createAdmin(), '/api/admin/magic-ai/image', [
            'prompt' => 'A red t-shirt',
            'size' => '16:9',
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_requires_authentication(): void
    {
        $this->assertSame(401, $this->publicPost('/api/admin/magic-ai/content', ['prompt' => 'x'])->getStatusCode());
        $this->assertSame(401, $this->publicPost('/api/admin/magic-ai/image', ['prompt' => 'x', 'size' => '1:1'])->getStatusCode());
    }
}
