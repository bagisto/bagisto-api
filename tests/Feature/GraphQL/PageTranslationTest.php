<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\CMS\Models\Page;
use Webkul\CMS\Models\PageTranslation;

class PageTranslationTest extends GraphQLTestCase
{
    /**
     * The pageTranslations query lists the CMS page translations, including a new one.
     */
    public function test_page_translations_query_lists_translations(): void
    {
        $translation = $this->seedTranslation();

        $response = $this->graphQL(<<<'GQL'
            query {
              pageTranslations(first: 100) {
                totalCount
                edges {
                  node {
                    _id
                    pageTitle
                    urlKey
                    locale
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        $node = collect($response->json('data.pageTranslations.edges'))
            ->pluck('node')
            ->firstWhere('_id', $translation->id);

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.pageTranslations.totalCount'))->toBe(PageTranslation::count())
            ->and($node['pageTitle'])->toBe($translation->page_title)
            ->and($node['urlKey'])->toBe($translation->url_key)
            ->and($node['locale'])->toBe('en');
    }

    /**
     * The pageTranslation query returns one translation by its IRI.
     */
    public function test_page_translation_query_returns_translation(): void
    {
        $translation = $this->seedTranslation();

        $response = $this->graphQL(<<<GQL
            query {
              pageTranslation(id: "/api/shop/page_translations/{$translation->id}") {
                _id
                pageTitle
                urlKey
                htmlContent
                cmsPageId
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.pageTranslation._id'))->toBe($translation->id)
            ->and($response->json('data.pageTranslation.pageTitle'))->toBe($translation->page_title)
            ->and($response->json('data.pageTranslation.urlKey'))->toBe($translation->url_key)
            ->and($response->json('data.pageTranslation.htmlContent'))->toBe('<p>Test content</p>')
            ->and((int) $response->json('data.pageTranslation.cmsPageId'))->toBe($translation->cms_page_id);
    }

    /**
     * The pageTranslation query reports an error for an unknown translation.
     */
    public function test_page_translation_query_rejects_an_unknown_id(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              pageTranslation(id: "/api/shop/page_translations/999999999") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.pageTranslation'))->toBeNull();
    }

    /**
     * Create a CMS page with an English translation.
     */
    private function seedTranslation(): PageTranslation
    {
        $page = Page::create([
            'channels' => '1',
            'layout' => null,
        ]);

        return PageTranslation::create([
            'cms_page_id' => $page->id,
            'locale' => 'en',
            'page_title' => 'GraphQL Page '.uniqid(),
            'url_key' => 'graphql-page-'.uniqid(),
            'html_content' => '<p>Test content</p>',
            'meta_title' => 'Meta',
            'meta_keywords' => 'meta,keys',
            'meta_description' => 'meta desc',
        ]);
    }
}
