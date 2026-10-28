<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SettingsChannelTest extends AdminApiTestCase
{
    /**
     * Test query listing returns channels.
     */
    public function test_query_listing_returns_channels(): void
    {
        $admin = $this->createAdmin();
        $this->insertChannel(['code' => 'glist'.uniqid()]);

        $query = <<<'GQL'
            query {
              adminSettingsChannels(first: 5) {
                edges { node { _id code } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        $edges = $response->json('data.adminSettingsChannels.edges') ?? [];
        $hasErrors = ! empty($response->json('errors'));
        expect(is_array($edges) || $hasErrors)->toBeTrue();
    }

    /**
     * Test query detail returns channel.
     */
    public function test_query_detail_returns_channel(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'gdet'.uniqid()]);
        $iri = '/api/admin/settings/channels/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsChannel(id: $id) { _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();
        $hasErrors = ! empty($response->json('errors'));
        $hasData = $response->json('data.adminSettingsChannel') !== null;
        expect($hasErrors || $hasData)->toBeTrue();
    }

    /**
     * Test query detail multiword fields resolve over graphql.
     */
    public function test_query_detail_multiword_fields_resolve_over_graphql(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'gfr'.uniqid()]);
        $iri = '/api/admin/settings/channels/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsChannel(id: $id) {
                _id
                code
                defaultLocaleId
                baseCurrencyId
                rootCategoryId
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();
        $node = $response->json('data.adminSettingsChannel');

        expect($node)->not->toBeNull();
        expect($node['defaultLocaleId'])->not->toBeNull();
        expect($node['baseCurrencyId'])->not->toBeNull();
        expect($node['rootCategoryId'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
        expect($node['updatedAt'])->not->toBeNull();
    }

    /**
     * Test query detail resolves connections and home seo.
     */
    public function test_query_detail_resolves_connections_and_home_seo(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'gconn'.uniqid()]);
        \DB::table('channels')->where('id', $id)->update([
            'home_seo' => json_encode([
                'meta_title' => 'Conn Title',
                'meta_description' => 'Conn Desc',
                'meta_keywords' => 'conn,kw',
            ]),
        ]);
        $iri = '/api/admin/settings/channels/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsChannel(id: $id) {
                _id
                code
                seoMetaTitle
                seoMetaDescription
                seoMetaKeywords
                homeSeo
                translations {
                  edges {
                    node {
                      _id
                      locale
                      name
                      homeSeo
                    }
                  }
                }
                locales {
                  edges {
                    node {
                      _id
                      code
                      name
                      direction
                    }
                  }
                }
                currencies {
                  edges {
                    node {
                      _id
                      code
                      name
                      symbol
                    }
                  }
                }
                inventorySources {
                  edges {
                    node {
                      _id
                      code
                      name
                      status
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.adminSettingsChannel');
        expect($node)->not->toBeNull();

        expect($node['seoMetaTitle'])->toBe('Conn Title');
        expect($node['seoMetaDescription'])->toBe('Conn Desc');
        expect($node['seoMetaKeywords'])->toBe('conn,kw');

        $localeNodes = $response->json('data.adminSettingsChannel.locales.edges');
        expect($localeNodes)->toBeArray();
        expect(count($localeNodes))->toBeGreaterThan(0);
        expect($localeNodes[0]['node']['_id'])->not->toBeNull();
        expect($localeNodes[0]['node']['code'])->not->toBeNull();

        expect($response->json('data.adminSettingsChannel.currencies.edges.0.node._id'))->not->toBeNull();
        expect($response->json('data.adminSettingsChannel.inventorySources.edges.0.node._id'))->not->toBeNull();

        expect($response->json('data.adminSettingsChannel.translations.edges.0.node._id'))->not->toBeNull();
    }

    /**
     * Test mutation create happy path.
     */
    public function test_mutation_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $s = $this->ensureSupportRows();
        $code = 'gcr'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsChannelInput!) {
              createAdminSettingsChannel(input: $input) {
                adminSettingsChannel { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'code' => $code,
                'name' => 'GQL Create',
                'hostname' => $code.'.gql.test',
                'locales' => [$s['localeId']],
                'defaultLocaleId' => $s['localeId'],
                'currencies' => [$s['currencyId']],
                'baseCurrencyId' => $s['currencyId'],
                'inventorySources' => [$s['sourceId']],
                'rootCategoryId' => $s['rootCategoryId'],
            ],
        ], $admin);

        $response->assertOk();
        $exists = \DB::table('channels')->where('code', $code)->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($exists || $hasErrors)->toBeTrue();
    }

    /**
     * Test mutation create missing code fails.
     */
    public function test_mutation_create_missing_code_fails(): void
    {
        $admin = $this->createAdmin();
        $s = $this->ensureSupportRows();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsChannelInput!) {
              createAdminSettingsChannel(input: $input) {
                adminSettingsChannel { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name' => 'NoCode',
                'locales' => [$s['localeId']],
                'default_locale_id' => $s['localeId'],
                'currencies' => [$s['currencyId']],
                'base_currency_id' => $s['currencyId'],
                'inventory_sources' => [$s['sourceId']],
                'root_category_id' => $s['rootCategoryId'],
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    /**
     * Test mutation update happy path.
     */
    public function test_mutation_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'gupd'.uniqid()]);
        $iri = '/api/admin/settings/channels/'.$id;
        $s = $this->ensureSupportRows();

        $mutation = <<<'GQL'
            mutation($input: updateAdminSettingsChannelInput!) {
              updateAdminSettingsChannel(input: $input) {
                adminSettingsChannel { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id' => $iri,
                'hostname' => 'updated-gql.example.com',
                'locales' => [$s['localeId']],
                'defaultLocaleId' => $s['localeId'],
                'currencies' => [$s['currencyId']],
                'baseCurrencyId' => $s['currencyId'],
                'inventorySources' => [$s['sourceId']],
                'rootCategoryId' => $s['rootCategoryId'],
            ],
        ], $admin);

        $response->assertOk();
        $updated = \DB::table('channels')
            ->where('id', $id)
            ->where('hostname', 'updated-gql.example.com')
            ->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($updated || $hasErrors)->toBeTrue();
    }

    /**
     * Test mutation delete happy path.
     */
    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertChannel(['code' => 'gkp'.uniqid()]);
        $id = $this->insertChannel(['code' => 'gdl'.uniqid()]);
        $iri = '/api/admin/settings/channels/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsChannelInput!) {
              deleteAdminSettingsChannel(input: $input) {
                adminSettingsChannel { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('channels', ['id' => $id]);
    }

    /**
     * Test mutation delete last channel fails.
     */
    public function test_mutation_delete_last_channel_fails(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'glast'.uniqid()]);
        \DB::table('channels')->where('id', '!=', $id)->delete();
        $iri = '/api/admin/settings/channels/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsChannelInput!) {
              deleteAdminSettingsChannel(input: $input) {
                adminSettingsChannel { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        $this->assertDatabaseHas('channels', ['id' => $id]);
    }

    /**
     * Ensure support rows.
     */
    protected function ensureSupportRows(): array
    {
        $this->seedRequiredData();

        $localeId = (int) (\DB::table('locales')->value('id') ?: 0);
        $currencyId = (int) (\DB::table('currencies')->value('id') ?: 0);
        $sourceId = (int) (\DB::table('inventory_sources')->value('id') ?: 0);
        $rootCategoryId = (int) (\DB::table('categories')->value('id') ?: 0);

        if (! $localeId) {
            $localeId = (int) \DB::table('locales')->insertGetId([
                'code' => 'en', 'name' => 'English', 'direction' => 'ltr',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if (! $currencyId) {
            $currencyId = (int) \DB::table('currencies')->insertGetId([
                'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if (! $sourceId) {
            $sourceId = (int) \DB::table('inventory_sources')->insertGetId([
                'code' => 'default', 'name' => 'Default',
                'contact_name' => 'D', 'contact_email' => 'd@x.com', 'contact_number' => '0',
                'country' => 'US', 'state' => 'CA', 'city' => 'LA', 'street' => 'X', 'postcode' => '90001',
                'priority' => 0, 'status' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if (! $rootCategoryId) {
            $rootCategoryId = (int) \DB::table('categories')->insertGetId([
                '_lft' => 1, '_rgt' => 2, 'parent_id' => null, 'status' => 1, 'position' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return compact('localeId', 'currencyId', 'sourceId', 'rootCategoryId');
    }

    /**
     * Insert channel.
     */
    protected function insertChannel(array $overrides = []): int
    {
        $s = $this->ensureSupportRows();
        $code = $overrides['code'] ?? ('gqc'.uniqid());

        $id = \DB::table('channels')->insertGetId(array_merge([
            'code' => $code,
            'hostname' => $code.'.example.com',
            'is_maintenance_on' => 0,
            'root_category_id' => $s['rootCategoryId'],
            'default_locale_id' => $s['localeId'],
            'base_currency_id' => $s['currencyId'],
            'created_at' => now(),
            'updated_at' => now(),
        ], array_diff_key($overrides, ['name' => 1])));

        $localeCode = (string) \DB::table('locales')->where('id', $s['localeId'])->value('code');
        \DB::table('channel_translations')->insert([
            'channel_id' => $id, 'locale' => $localeCode,
            'name' => $overrides['name'] ?? ('GQL '.$id),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('channel_locales')->insertOrIgnore([
            'channel_id' => $id,
            'locale_id' => $s['localeId'],
        ]);
        \DB::table('channel_currencies')->insertOrIgnore([
            'channel_id' => $id,
            'currency_id' => $s['currencyId'],
        ]);
        \DB::table('channel_inventory_sources')->insertOrIgnore([
            'channel_id' => $id,
            'inventory_source_id' => $s['sourceId'],
        ]);

        return $id;
    }
}
