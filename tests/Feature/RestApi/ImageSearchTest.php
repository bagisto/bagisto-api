<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\MagicAI\Facades\MagicAI;

class ImageSearchTest extends RestApiTestCase
{
    /**
     * Test image search stores the image and returns tensorflow keywords when ai is off.
     */
    public function test_image_search_stores_the_image_and_returns_tensorflow_keywords_when_ai_is_off(): void
    {
        $this->seedRequiredData();
        $this->setConfig([
            'magic_ai.general.settings.enabled' => '0',
            'magic_ai.storefront_features.image_search.enabled' => '0',
        ]);

        $response = $this->post('/api/shop/image-searches', [
            'image' => UploadedFile::fake()->image('product.png'),
        ], [
            ...$this->storefrontHeaders(),
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJsonPath('keywords', '')
            ->assertJsonPath('engine', 'tensorflow');

        expect($response->json('imageUrl'))->toContain('product-search/');
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($response->json('imageUrl'), PHP_URL_PATH)));
    }

    /**
     * Test image search requires an image.
     */
    public function test_image_search_requires_an_image(): void
    {
        $this->seedRequiredData();

        $response = $this->postJson('/api/shop/image-searches', [], $this->storefrontHeaders());

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    /**
     * Test image search uses magic ai when both features are enabled.
     */
    public function test_image_search_uses_magic_ai_when_both_features_are_enabled(): void
    {
        $this->seedRequiredData();
        $this->setConfig([
            'magic_ai.general.settings.enabled' => '1',
            'magic_ai.storefront_features.image_search.enabled' => '1',
        ]);

        MagicAI::shouldReceive('analyzeImage')
            ->once()
            ->andReturn('blue cotton shirt');

        $response = $this->post('/api/shop/image-searches', [
            'image' => UploadedFile::fake()->image('shirt.png'),
        ], [
            ...$this->storefrontHeaders(),
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJsonPath('keywords', 'blue cotton shirt')
            ->assertJsonPath('engine', 'ai');
    }

    /**
     * Test image search rejects non image files.
     */
    public function test_image_search_rejects_non_image_files(): void
    {
        $this->seedRequiredData();

        $response = $this->post('/api/shop/image-searches', [
            'image' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain'),
        ], [
            ...$this->storefrontHeaders(),
            'Accept' => 'application/json',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
        Storage::fake('public');
    }
}
