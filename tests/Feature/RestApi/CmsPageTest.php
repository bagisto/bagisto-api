<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\CMS\Models\Page;
use Webkul\CMS\Models\PageTranslation;
use Webkul\Core\Models\Channel;

class CmsPageTest extends RestApiTestCase
{
    /**
     * Create test CMS page with translation
     */
    private function createTestCmsPage(): array
    {
        // Ensure we have required data
        $this->seedRequiredData();

        // Create a channel if none exists
        $channel = Channel::first();
        if (! $channel) {
            $channel = Channel::factory()->create();
        }

        // Create CMS page
        $page = Page::factory()->create([
            'layout' => 'default',
        ]);

        // Attach channel
        $page->channels()->attach($channel->id);

        // Create translation
        $translation = PageTranslation::factory()->create([
            'cms_page_id'      => $page->id,
            'locale'           => 'en',
            'page_title'       => 'Test About Us Page',
            'url_key'          => 'test-about-us',
            'html_content'     => '<p>This is a test about us page content</p>',
            'meta_title'       => 'Test About Us',
            'meta_description' => 'Test meta description',
            'meta_keywords'    => 'test, about, us',
        ]);

        return compact('page', 'translation', 'channel');
    }

    // ── Collection ────────────────────────────────────────────

    /**
     * Test: GET /api/shop/pages returns collection
     */
    public function test_get_cms_pages_collection(): void
    {
        $this->createTestCmsPage();

        $response = $this->publicGet('/api/shop/pages');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBeGreaterThanOrEqual(1);

        // Check first item has expected structure
        $firstPage = $json[0];
        expect($firstPage)->toHaveKey('id');
        expect($firstPage)->toHaveKey('_id');
        expect($firstPage)->toHaveKey('layout');
        expect($firstPage)->toHaveKey('createdAt');
        expect($firstPage)->toHaveKey('updatedAt');
        expect($firstPage)->toHaveKey('translation');
    }

    /**
     * Test: GET /api/shop/pages returns all required fields
     */
    public function test_get_cms_pages_contains_all_fields(): void
    {
        $this->createTestCmsPage();

        $response = $this->publicGet('/api/shop/pages');

        $response->assertOk();
        $json = $response->json();

        expect($json)->not()->toBeEmpty();

        $page = $json[0];

        // Main page fields
        expect($page['id'])->toMatch('/^\/api\/shop\/pages\/\d+$/');
        expect($page['_id'])->toBeInt();
        expect($page['layout'])->toBe('default');
        expect($page['createdAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($page['updatedAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');

        // Translation fields
        expect($page['translation'])->toHaveKey('id');
        expect($page['translation'])->toHaveKey('_id');
        expect($page['translation'])->toHaveKey('pageTitle');
        expect($page['translation'])->toHaveKey('urlKey');
        expect($page['translation'])->toHaveKey('htmlContent');
        expect($page['translation'])->toHaveKey('metaTitle');
        expect($page['translation'])->toHaveKey('metaDescription');
        expect($page['translation'])->toHaveKey('metaKeywords');
        expect($page['translation'])->toHaveKey('locale');
    }

    // ── Single Item ───────────────────────────────────────────

    /**
     * Test: GET /api/shop/pages/{id} returns single page
     */
    public function test_get_single_cms_page(): void
    {
        $testData = $this->createTestCmsPage();

        $response = $this->publicGet('/api/shop/pages/'.$testData['page']->id);

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect($json)->toHaveKey('_id');
        expect($json)->toHaveKey('layout');
        expect($json)->toHaveKey('createdAt');
        expect($json)->toHaveKey('updatedAt');
        expect($json)->toHaveKey('translation');

        expect($json['_id'])->toBe($testData['page']->id);
        expect($json['layout'])->toBe('default');
    }

    /**
     * Test: GET /api/shop/pages/{id} with translation data
     */
    public function test_get_single_cms_page_with_translation(): void
    {
        $testData = $this->createTestCmsPage();

        $response = $this->publicGet('/api/shop/pages/'.$testData['page']->id);

        $response->assertOk();
        $json = $response->json();

        $translation = $json['translation'];
        expect($translation['pageTitle'])->toBe('Test About Us Page');
        expect($translation['urlKey'])->toBe('test-about-us');
        expect($translation['htmlContent'])->toContain('test about us page content');
        expect($translation['metaTitle'])->toBe('Test About Us');
        expect($translation['metaDescription'])->toBe('Test meta description');
        expect($translation['metaKeywords'])->toBe('test, about, us');
        expect($translation['locale'])->toBe('en');
    }

    /**
     * Test: GET /api/shop/pages/{id} with invalid id returns 404
     */
    public function test_get_cms_page_not_found(): void
    {
        $response = $this->publicGet('/api/shop/pages/999999');

        $response->assertNotFound();
    }

    // ── Timestamps ───────────────────────────────────────────

    /**
     * Test: Timestamps are returned in ISO8601 format
     */
    public function test_cms_page_timestamps_are_iso8601_format(): void
    {
        $this->createTestCmsPage();

        $response = $this->publicGet('/api/shop/pages');

        $response->assertOk();
        $json = $response->json();

        $page = $json[0];

        // Verify ISO8601 format (should contain 'T' and timezone)
        expect($page['createdAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($page['updatedAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    // ── Multiple Pages ───────────────────────────────────────

    /**
     * Test: Multiple CMS pages can be retrieved
     */
    public function test_get_multiple_cms_pages(): void
    {
        // Create multiple pages
        $this->createTestCmsPage();
        $this->createTestCmsPage();

        $response = $this->publicGet('/api/shop/pages');

        $response->assertOk();
        $json = $response->json();

        expect(count($json))->toBeGreaterThanOrEqual(2);

        // Each page should have translation
        foreach ($json as $page) {
            expect($page)->toHaveKey('translation');
            expect($page['translation']['pageTitle'])->not()->toBeNull();
        }
    }

    // ── ID Format ───────────────────────────────────────────

    /**
     * Test: Page IDs are in correct IRI format
     */
    public function test_cms_page_id_format(): void
    {
        $testData = $this->createTestCmsPage();

        $response = $this->publicGet('/api/shop/pages/'.$testData['page']->id);

        $response->assertOk();
        $json = $response->json();

        // Check id format: /api/shop/pages/{id}
        expect($json['id'])->toMatch('/^\/api\/shop\/pages\/\d+$/');

        // Check translation id format: /api/shop/page_translations/{id}
        expect($json['translation']['id'])->toMatch('/^\/api\/shop\/page_translations\/\d+$/');
    }
}
