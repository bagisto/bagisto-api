<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\MagicAI\Facades\MagicAI;

class MagicAiTest extends AdminApiTestCase
{
    private const CONTENT = <<<'GQL'
        mutation ($input: createAdminMagicAiContentInput!) {
          createAdminMagicAiContent(input: $input) {
            adminMagicAiContent {
              content
            }
          }
        }
    GQL;

    private const IMAGE = <<<'GQL'
        mutation ($input: createAdminMagicAiImageInput!) {
          createAdminMagicAiImage(input: $input) {
            adminMagicAiImage {
              images
            }
          }
        }
    GQL;

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

    public function test_content_mutation_returns_generated_text(): void
    {
        $this->enable('text_generation');

        MagicAI::shouldReceive('generateContent')->once()->andReturn('A soft red cotton tee.');

        $response = $this->adminGraphQL(self::CONTENT, ['input' => ['prompt' => 'Describe a red t-shirt']], $this->createAdmin());

        $response->assertOk();
        $this->assertNull($response->json('errors'));
        $this->assertSame('A soft red cotton tee.', $response->json('data.createAdminMagicAiContent.adminMagicAiContent.content'));
    }

    public function test_image_mutation_returns_generated_images(): void
    {
        $this->enable('image_generation');

        MagicAI::shouldReceive('generateImage')->once()->andReturn([['url' => 'data:image/png;base64,AAA']]);

        $response = $this->adminGraphQL(self::IMAGE, ['input' => ['prompt' => 'A red t-shirt', 'size' => '1:1']], $this->createAdmin());

        $response->assertOk();
        $this->assertNull($response->json('errors'));
        $this->assertCount(1, $response->json('data.createAdminMagicAiImage.adminMagicAiImage.images'));
    }

    public function test_content_mutation_validates_prompt(): void
    {
        $this->enable('text_generation');

        $response = $this->adminGraphQL(self::CONTENT, ['input' => ['model' => 'gpt-4o-mini']], $this->createAdmin());

        $this->assertNotEmpty($response->json('errors'));
    }

    public function test_content_mutation_is_refused_when_disabled(): void
    {
        $this->setConfig('magic_ai.general.settings.enabled', '0');

        $response = $this->adminGraphQL(self::CONTENT, ['input' => ['prompt' => 'x']], $this->createAdmin());

        $this->assertNotEmpty($response->json('errors'));
    }

    public function test_content_mutation_requires_authentication(): void
    {
        $response = $this->adminGraphQL(self::CONTENT, ['input' => ['prompt' => 'x']]);

        $this->assertNotEmpty($response->json('errors'));
    }
}
