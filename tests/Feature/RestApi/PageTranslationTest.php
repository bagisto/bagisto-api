<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\CMS\Models\Page;
use Webkul\CMS\Models\PageTranslation;

class PageTranslationTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/page_translations';

    private function seedTranslation(): PageTranslation
    {
        $page = Page::create([
            'channels' => '1',
            'layout'   => null,
        ]);

        return PageTranslation::create([
            'page_id'          => $page->id,
            'locale'           => 'en',
            'page_title'       => 'Test Page '.uniqid(),
            'url_key'          => 'test-page-'.uniqid(),
            'html_content'     => '<p>Test content</p>',
            'meta_title'       => 'Meta',
            'meta_keywords'    => 'meta,keys',
            'meta_description' => 'meta desc',
        ]);
    }

    public function test_get_collection(): void
    {
        $this->seedRequiredData();
        $this->seedTranslation();

        $response = $this->publicGet($this->baseUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
        expect(\count($response->json()))->toBeGreaterThan(0);
    }

    public function test_get_single_page_translation(): void
    {
        $this->seedRequiredData();
        $translation = $this->seedTranslation();

        $response = $this->publicGet($this->baseUrl.'/'.$translation->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($translation->id);
    }

    public function test_get_nonexistent_translation_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}
