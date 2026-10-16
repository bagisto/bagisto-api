<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ImageSearchTest extends GraphQLTestCase
{
    private const MUTATION = <<<'GQL'
        mutation imageSearch($input: createImageSearchInput!) {
          createImageSearch(input: $input) {
            imageSearch {
              imageUrl
              keywords
              engine
            }
          }
        }
    GQL;

    public function test_base64_image_is_stored(): void
    {
        $this->seedRequiredData();
        Storage::fake();

        $response = $this->graphQL(self::MUTATION, ['input' => [
            'image' => 'data:image/png;base64,'.base64_encode(UploadedFile::fake()->image('shoe.png')->get()),
        ]]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createImageSearch.imageSearch.imageUrl'))->toContain('product-search/');
        expect($response->json('data.createImageSearch.imageSearch.engine'))->toBe('tensorflow');
    }

    public function test_a_non_image_is_refused(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL(self::MUTATION, ['input' => ['image' => 'data:image/png;base64,'.base64_encode('not an image')]]);

        expect($response->json('errors'))->not->toBeNull();
    }
}
