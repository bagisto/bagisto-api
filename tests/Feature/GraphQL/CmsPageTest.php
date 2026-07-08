<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\CMS\Models\Page;
use Webkul\CMS\Models\PageTranslation;
use Webkul\Core\Models\Channel;

class CmsPageTest extends GraphQLTestCase
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
            'cms_page_id' => $page->id,
            'locale' => 'en',
            'page_title' => 'Test About Us Page',
            'url_key' => 'test-about-us',
            'html_content' => '<p>This is a test about us page content</p>',
            'meta_title' => 'Test About Us',
            'meta_description' => 'Test meta description',
            'meta_keywords' => 'test, about, us',
        ]);

        return compact('page', 'translation', 'channel');
    }

    /**
     * Test: Query all CMS pages collection
     */
    public function test_get_cms_pages_collection(): void
    {
        // Create test data
        $this->createTestCmsPage();

        $query = <<<'GQL'
            query getCmsPages {
              pages(first: 30) {
                edges {
                  node {
                    id
                    _id
                    layout
                    createdAt
                    updatedAt
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'pages' => [
                    'edges' => [
                        '*' => [
                            'node' => [
                                'id',
                                '_id',
                                'layout',
                                'createdAt',
                                'updatedAt',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $edges = $response->json('data.pages.edges');
        expect($edges)->not()->toBeEmpty();
    }

    /**
     * Test: Query single CMS page by ID
     */
    public function test_get_cms_page_by_id(): void
    {
        $testData = $this->createTestCmsPage();
        $pageId = '/api/shop/pages/'.$testData['page']->id;

        $query = <<<GQL
            query getCmsPage {
              page(id: "{$pageId}") {
                id
                _id
                layout
                createdAt
                updatedAt
                translation {
                  id
                  _id
                  pageTitle
                  urlKey
                  htmlContent
                  metaTitle
                  metaDescription
                  metaKeywords
                  locale
                  cmsPageId
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.page');

        expect($data['_id'])->toBe($testData['page']->id);
        expect($data['layout'])->toBe('default');
        expect($data['translation']['pageTitle'])->toBe('Test About Us Page');
        expect($data['translation']['urlKey'])->toBe('test-about-us');
        expect($data['translation']['locale'])->toBe('en');
    }

    /**
     * Test: Query CMS page by URL key
     */
    public function test_get_cms_page_by_url_key(): void
    {
        $this->createTestCmsPage();

        $query = <<<'GQL'
            query getCmsPageByUrlKey {
              pageByUrlKeypages(urlKey: "test-about-us") {
                id
                _id
                translation {
                  pageTitle
                  urlKey
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.pageByUrlKeypages');

        expect($data)->not()->toBeEmpty();
        expect($data[0]['translation']['urlKey'])->toBe('test-about-us');
    }

    /**
     * Test: Timestamps are returned in ISO8601 format
     */
    public function test_cms_page_timestamps_are_iso8601_format(): void
    {
        $this->createTestCmsPage();

        $query = <<<'GQL'
            query getCmsPages {
              pages(first: 1) {
                edges {
                  node {
                    createdAt
                    updatedAt
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $page = $response->json('data.pages.edges.0.node');

        // Verify ISO8601 format (should contain 'T' and timezone)
        expect($page['createdAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($page['updatedAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    /**
     * Test: Query returns appropriate error for invalid ID
     */
    public function test_invalid_cms_page_id_returns_error(): void
    {
        $query = <<<'GQL'
            query getCmsPage {
              page(id: "/api/shop/pages/99999") {
                id
                _id
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        expect($response->json('data.page'))->toBeNull();
    }

    /**
     * Test: Translation contains all expected fields
     */
    public function test_cms_page_translation_contains_all_fields(): void
    {
        $testData = $this->createTestCmsPage();
        $pageId = '/api/shop/pages/'.$testData['page']->id;

        $query = <<<GQL
            query getCmsPage {
              page(id: "{$pageId}") {
                translation {
                  id
                  _id
                  pageTitle
                  urlKey
                  htmlContent
                  metaTitle
                  metaDescription
                  metaKeywords
                  locale
                  cmsPageId
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $translation = $response->json('data.page.translation');

        expect($translation)->toHaveKeys([
            'id',
            '_id',
            'pageTitle',
            'urlKey',
            'htmlContent',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'locale',
            'cmsPageId',
        ]);

        expect($translation['pageTitle'])->toBe('Test About Us Page');
        expect($translation['htmlContent'])->toContain('test about us page content');
    }

    /**
     * Test: Query all CMS pages with edges/node pattern including translation
     */
    public function test_get_cms_pages_with_edges_and_translation(): void
    {
        $testData = $this->createTestCmsPage();

        $query = <<<'GQL'
            query getCmsPagesDetails {
              pages(first: 30) {
                edges {
                  node {
                    id
                    _id
                    layout
                    createdAt
                    updatedAt
                    translation {
                      id
                      _id
                      pageTitle
                      urlKey
                      htmlContent
                      metaTitle
                      metaDescription
                      metaKeywords
                      locale
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'pages' => [
                    'edges' => [
                        '*' => [
                            'node' => [
                                'id',
                                '_id',
                                'layout',
                                'createdAt',
                                'updatedAt',
                                'translation' => [
                                    'id',
                                    '_id',
                                    'pageTitle',
                                    'urlKey',
                                    'htmlContent',
                                    'metaTitle',
                                    'metaDescription',
                                    'metaKeywords',
                                    'locale',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $edges = $response->json('data.pages.edges');
        expect($edges)->not()->toBeEmpty();

        // Verify the created page is present in the results
        $found = collect($edges)->first(fn ($edge) => $edge['node']['_id'] == $testData['page']->id);
        expect($found)->not()->toBeNull();
        expect($found['node']['translation']['pageTitle'])->toBe('Test About Us Page');
        expect($found['node']['translation']['urlKey'])->toBe('test-about-us');
        expect($found['node']['translation']['htmlContent'])->toContain('test about us page content');
        expect($found['node']['translation']['metaTitle'])->toBe('Test About Us');
        expect($found['node']['translation']['metaKeywords'])->toBe('test, about, us');
        expect($found['node']['translation']['locale'])->toBe('en');
    }

    /**
     * Test: Multiple CMS pages can be queried
     */
    public function test_query_multiple_cms_pages(): void
    {
        // Create multiple pages
        $this->createTestCmsPage();
        $this->createTestCmsPage();

        $query = <<<'GQL'
            query getCmsPages {
              pages(first: 30) {
                edges {
                  node {
                    id
                    _id
                    translation {
                      pageTitle
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $edges = $response->json('data.pages.edges');

        expect(count($edges))->toBeGreaterThanOrEqual(2);
    }
}
