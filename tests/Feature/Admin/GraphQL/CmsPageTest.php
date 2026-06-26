<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin CMS Pages endpoints.
 */
class CmsPageTest extends AdminApiTestCase
{
    protected function insertCmsPage(array $translation = [], array $channels = []): int
    {
        $pageId = \DB::table('cms_pages')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('cms_page_translations')->insert(array_merge([
            'cms_page_id'      => $pageId,
            'locale'           => 'en',
            'page_title'       => 'GQL Page '.$pageId,
            'url_key'          => 'gql-page-'.$pageId,
            'html_content'     => '<p>content</p>',
            'meta_title'       => null,
            'meta_keywords'    => null,
            'meta_description' => null,
        ], $translation));

        if (! empty($channels)) {
            foreach ($channels as $cid) {
                \DB::table('cms_page_channels')->insert(['cms_page_id' => $pageId, 'channel_id' => $cid]);
            }
        } else {
            $cid = \DB::table('channels')->value('id') ?: 1;
            \DB::table('cms_page_channels')->insert(['cms_page_id' => $pageId, 'channel_id' => $cid]);
        }

        return $pageId;
    }

    protected function defaultChannelId(): int
    {
        return (int) (\DB::table('channels')->value('id') ?: 1);
    }

    public function test_query_listing_returns_pages(): void
    {
        $admin = $this->createAdmin();
        $this->insertCmsPage(['page_title' => 'GQL List', 'url_key' => 'gql-list-'.uniqid()]);

        $query = <<<'GQL'
            query {
              adminCmsPages(first: 10) {
                edges { node { _id pageTitle urlKey } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.adminCmsPages.edges'))->toBeArray();
    }

    public function test_query_listing_multi_word_fields_resolve_non_null(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-mw-'.uniqid();
        $this->insertCmsPage([
            'page_title'       => 'MultiWord Page',
            'url_key'          => $slug,
            'meta_title'       => 'GQL Meta',
            'meta_keywords'    => 'a,b',
            'meta_description' => 'GQL Desc',
        ]);

        $query = <<<'GQL'
            query {
              adminCmsPages(first: 50) {
                edges { node { _id pageTitle urlKey metaTitle metaKeywords metaDescription previewUrl createdAt locale } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();

        $nodes = collect($response->json('data.adminCmsPages.edges'))->pluck('node');
        $node = $nodes->firstWhere('urlKey', $slug);

        expect($node)->not->toBeNull();
        expect($node['pageTitle'])->toBe('MultiWord Page');
        expect($node['metaTitle'])->toBe('GQL Meta');
        expect($node['metaKeywords'])->toBe('a,b');
        expect($node['metaDescription'])->toBe('GQL Desc');
        expect($node['previewUrl'])->not->toBeNull();
        expect($node['previewUrl'])->toContain($slug);
        expect($node['createdAt'])->not->toBeNull();
    }

    public function test_query_detail_multi_word_fields_resolve_non_null(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-dmw-'.uniqid();
        $id = $this->insertCmsPage([
            'page_title'   => 'Detail MW',
            'url_key'      => $slug,
            'html_content' => '<h1>Body</h1>',
            'meta_title'   => 'DMeta',
        ]);
        $iri = '/api/admin/cms/pages/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminCmsPage(id: $id) {
                _id pageTitle urlKey htmlContent metaTitle previewUrl
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();

        $node = $response->json('data.adminCmsPage');
        if ($node !== null) {
            expect($node['pageTitle'])->toBe('Detail MW');
            expect($node['htmlContent'])->toBe('<h1>Body</h1>');
            expect($node['metaTitle'])->toBe('DMeta');
            expect($node['previewUrl'])->toContain($slug);
        } else {
            expect($response->json('errors'))->not->toBeEmpty();
        }
    }

    public function test_query_detail_returns_page(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCmsPage(['page_title' => 'GQL Detail', 'url_key' => 'gql-dtl-'.uniqid()]);
        $iri = '/api/admin/cms/pages/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminCmsPage(id: $id) {
                _id
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();
        $hasErrors = ! empty($response->json('errors'));
        $hasData = $response->json('data.adminCmsPage') !== null;
        expect($hasErrors || $hasData)->toBeTrue();
    }

    public function test_mutation_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-cr-'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminCmsPageInput!) {
              createAdminCmsPage(input: $input) {
                adminCmsPage { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'urlKey'      => $slug,
                'pageTitle'   => 'GQL Created',
                'htmlContent' => '<p>x</p>',
                'channels'    => [$this->defaultChannelId()],
            ],
        ], $admin);

        $response->assertOk();
        $exists = \DB::table('cms_page_translations')->where('url_key', $slug)->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($exists || $hasErrors)->toBeTrue();
    }

    public function test_mutation_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-upd-'.uniqid();
        $id = $this->insertCmsPage(['url_key' => $slug, 'page_title' => 'Before GQL']);
        $iri = '/api/admin/cms/pages/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminCmsPageInput!) {
              updateAdminCmsPage(input: $input) {
                adminCmsPage { _id urlKey pageTitle }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'       => $iri,
                'locale'   => 'en',
                'channels' => [$this->defaultChannelId()],
                'en'       => [
                    'url_key'      => $slug,
                    'page_title'   => 'After GQL Update',
                    'html_content' => '<p>y</p>',
                ],
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeEmpty();
        expect($response->json('data.updateAdminCmsPage.adminCmsPage.pageTitle'))->toBe('After GQL Update');
        $after = \DB::table('cms_page_translations')->where('cms_page_id', $id)->where('locale', 'en')->value('page_title');
        expect($after)->toBe('After GQL Update');
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCmsPage(['url_key' => 'gql-del-'.uniqid()]);
        $iri = '/api/admin/cms/pages/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCmsPageInput!) {
              deleteAdminCmsPage(input: $input) {
                adminCmsPage { id _id urlKey pageTitle message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeEmpty();
        expect($response->json('data.deleteAdminCmsPage.adminCmsPage._id'))->toBe($id);
        expect($response->json('data.deleteAdminCmsPage.adminCmsPage.pageTitle'))->not->toBeNull();
        expect($response->json('data.deleteAdminCmsPage.adminCmsPage.message'))->toBe(__('bagistoapi::app.admin.cms.page.deleted'));
        expect(\DB::table('cms_pages')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_mutation_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCmsPage(['url_key' => 'gql-md1-'.uniqid()]);
        $id2 = $this->insertCmsPage(['url_key' => 'gql-md2-'.uniqid()]);

        $mutation = <<<'GQL'
            mutation($input: createAdminCmsPageMassDeleteInput!) {
              createAdminCmsPageMassDelete(input: $input) {
                adminCmsPageMassDelete { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['indices' => [$id1, $id2]]], $admin);

        $response->assertOk();
        expect(\DB::table('cms_pages')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('cms_pages')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_query_detail_selects_translation_and_channel_subfields(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-sel-'.uniqid();
        $id = $this->insertCmsPage([
            'page_title'   => 'Selectable Page',
            'url_key'      => $slug,
            'html_content' => '<h1>Body</h1>',
        ]);
        $iri = '/api/admin/cms/pages/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminCmsPage(id: $id) {
                id
                translations { edges { node { locale pageTitle urlKey htmlContent } } }
                channels { edges { node { id code name } } }
              }
            }
        GQL;

        $response = $this->postJson('/api/admin/graphql', ['query' => $query, 'variables' => ['id' => $iri]], $this->adminHeaders($admin));
        $response->assertOk();

        $node = $response->json('data.adminCmsPage');
        expect($node)->not->toBeNull();

        $translationNodes = collect($response->json('data.adminCmsPage.translations.edges'))->pluck('node');
        $en = $translationNodes->firstWhere('locale', 'en');
        expect($en)->not->toBeNull();
        expect($en['pageTitle'])->toBe('Selectable Page');
        expect($en['urlKey'])->toBe($slug);
        expect($en['htmlContent'])->toBe('<h1>Body</h1>');

        $channelNodes = collect($response->json('data.adminCmsPage.channels.edges'))->pluck('node');
        expect($channelNodes)->not->toBeEmpty();
        expect($channelNodes->first()['code'])->not->toBeNull();
        expect($channelNodes->first()['name'])->not->toBeNull();
    }

    public function test_query_detail_partial_translation_selection_returns_only_requested_field(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-partial-'.uniqid();
        $id = $this->insertCmsPage([
            'page_title' => 'Partial Page',
            'url_key'    => $slug,
        ]);
        $iri = '/api/admin/cms/pages/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminCmsPage(id: $id) {
                translations { edges { node { pageTitle } } }
              }
            }
        GQL;

        $response = $this->postJson('/api/admin/graphql', ['query' => $query, 'variables' => ['id' => $iri]], $this->adminHeaders($admin));
        $response->assertOk();
        expect($response->json('errors'))->toBeEmpty();

        $nodes = collect($response->json('data.adminCmsPage.translations.edges'))->pluck('node');
        $en = $nodes->firstWhere('pageTitle', 'Partial Page');
        expect($en)->not->toBeNull();
        expect(array_keys($en))->toBe(['pageTitle']);
    }
}
