<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\MagicAI\Facades\MagicAI;

class ImageSearchTest extends RestApiTestCase
{
    private function enableAiImageSearch(): void
    {
        $codes = [
            'magic_ai.general.settings.enabled' => null,
            'magic_ai.storefront_features.image_search.enabled' => core()->getRequestedChannelCode(),
        ];

        foreach ($codes as $code => $channel) {
            DB::table('core_config')->where('code', $code)->delete();
            DB::table('core_config')->insert([
                'code' => $code,
                'value' => '1',
                'channel_code' => $channel,
                'locale_code' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    public function test_upload_stores_the_image(): void
    {
        $this->seedRequiredData();
        Storage::fake();

        $response = $this->publicPost('/api/shop/image-searches', ['image' => UploadedFile::fake()->image('shoe.png')]);

        $response->assertCreated();
        expect($response->json('imageUrl'))->toContain('product-search/');
        expect($response->json('engine'))->toBe('tensorflow');
        expect(Storage::files('product-search'))->toHaveCount(1);
    }

    public function test_base64_image_in_a_json_body_is_stored(): void
    {
        $this->seedRequiredData();
        Storage::fake();

        $response = $this->publicPost('/api/shop/image-searches', [
            'image' => 'data:image/png;base64,'.base64_encode(UploadedFile::fake()->image('shoe.png')->get()),
        ]);

        $response->assertCreated();
        expect(Storage::files('product-search'))->toHaveCount(1);
    }

    public function test_keywords_come_from_magic_ai_when_switched_on(): void
    {
        $this->seedRequiredData();
        Storage::fake();
        $this->enableAiImageSearch();
        MagicAI::shouldReceive('analyzeImage')->once()->andReturn('red shoe, sneaker');

        $response = $this->publicPost('/api/shop/image-searches', ['image' => UploadedFile::fake()->image('shoe.png')]);

        $response->assertCreated();
        expect($response->json('engine'))->toBe('ai');
        expect($response->json('keywords'))->toBe('red shoe, sneaker');
    }

    public function test_a_non_image_is_refused(): void
    {
        $this->seedRequiredData();

        $this->publicPost('/api/shop/image-searches', [
            'image' => UploadedFile::fake()->create('clip.mp4', 20, 'video/mp4'),
        ])->assertStatus(422);
    }

    public function test_an_image_is_required(): void
    {
        $this->seedRequiredData();

        $this->publicPost('/api/shop/image-searches', [])->assertStatus(422);
    }
}
