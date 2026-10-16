<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\MagicAI\Facades\MagicAI;

class ImageSearchTest extends GraphQLTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
        Storage::fake('public');
    }

    private function mutation(): string
    {
        return <<<'GQL'
            mutation createImageSearch($input: createImageSearchInput!) {
                createImageSearch(input: $input) {
                    imageSearch {
                        imageUrl
                        keywords
                        engine
                    }
                }
            }
        GQL;
    }

    private function base64Image(): string
    {
        return 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    }

    public function test_image_search_over_graphql_stores_the_image_and_returns_tensorflow_keywords_when_ai_is_off(): void
    {
        $this->seedRequiredData();
        $this->setConfig([
            'magic_ai.general.settings.enabled' => '0',
            'magic_ai.storefront_features.image_search.enabled' => '0',
        ]);

        $response = $this->graphQL($this->mutation(), [
            'input' => [
                'image' => $this->base64Image(),
                'fileName' => 'product.png',
                'mimeType' => 'image/png',
            ],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.createImageSearch.imageSearch');

        expect($data['keywords'])->toBe('')
            ->and($data['engine'])->toBe('tensorflow')
            ->and($data['imageUrl'])->toContain('product-search/');

        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($data['imageUrl'], PHP_URL_PATH)));
    }

    public function test_image_search_over_graphql_uses_magic_ai_when_both_features_are_enabled(): void
    {
        $this->seedRequiredData();
        $this->setConfig([
            'magic_ai.general.settings.enabled' => '1',
            'magic_ai.storefront_features.image_search.enabled' => '1',
        ]);

        MagicAI::shouldReceive('analyzeImage')
            ->once()
            ->andReturn('blue cotton shirt');

        $response = $this->graphQL($this->mutation(), [
            'input' => [
                'image' => $this->base64Image(),
                'fileName' => 'shirt.png',
            ],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.createImageSearch.imageSearch');

        expect($data['keywords'])->toBe('blue cotton shirt')
            ->and($data['engine'])->toBe('ai');
    }

    public function test_image_search_over_graphql_rejects_an_invalid_payload(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL($this->mutation(), [
            'input' => [
                'image' => 'not-a-real-base64!!',
            ],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }
}
