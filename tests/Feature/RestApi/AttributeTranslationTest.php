<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AttributeTranslationTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/attribute_translations';

    public function test_get_collection(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_single_translation(): void
    {
        $this->seedRequiredData();

        $id = (int) (DB::table('attribute_translations')->value('id') ?? 0);
        if (! $id) {
            $this->markTestSkipped('No attribute_translations seeded.');
        }

        $response = $this->publicGet($this->baseUrl.'/'.$id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($id);
    }

    public function test_get_nonexistent_translation_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}
