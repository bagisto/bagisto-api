<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ChannelTest extends GraphQLTestCase
{
    /**
     * Get Channels - Basic
     */
    public function test_get_channels_basic(): void
    {
        $query = <<<'GQL'
            query getChannels {
              channels {
                edges {
                  node {
                    id
                    _id
                    code
                    hostname
                    timezone
                  }
                }
                pageInfo {
                  hasNextPage
                  endCursor
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.channels');

        $this->assertNotNull($data, 'channels response is null');
        $this->assertIsArray($data['edges'] ?? [], 'channels.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);

        if (! empty($data['edges'])) {
            $first = $data['edges'][0]['node'] ?? null;
            $this->assertNotNull($first, 'first channel node is null');
            $this->assertArrayHasKey('id', $first);
            $this->assertArrayHasKey('_id', $first);
            $this->assertArrayHasKey('code', $first);
            $this->assertArrayHasKey('hostname', $first);
            $this->assertArrayHasKey('timezone', $first);
        }
    }

    /**
     * Get Channels - Complete Details
     */
    public function test_get_channels_complete_details(): void
    {
        $query = <<<'GQL'
            query getChannels {
              channels {
                edges {
                  node {
                    id
                    _id
                    code
                    timezone
                    theme
                    hostname
                    logo
                    favicon
                    isMaintenanceOn
                    allowedIps
                    createdAt
                    updatedAt
                    logoUrl
                    faviconUrl
                    translation {
                      id
                      _id
                      channelId
                      locale
                      name
                      description
                      maintenanceModeText
                      createdAt
                      updatedAt
                    }
                    translations {
                      edges {
                        node {
                          id
                          _id
                          channelId
                          locale
                          name
                          description
                          maintenanceModeText
                          createdAt
                          updatedAt
                        }
                        cursor
                      }
                      pageInfo {
                        endCursor
                        startCursor
                        hasNextPage
                        hasPreviousPage
                      }
                      totalCount
                    }
                  }
                  cursor
                }
                pageInfo {
                  endCursor
                  startCursor
                  hasNextPage
                  hasPreviousPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.channels');

        $this->assertNotNull($data, 'channels response is null');
        $this->assertIsArray($data['edges'] ?? [], 'channels.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        // pageInfo expected keys
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);
        $this->assertArrayHasKey('startCursor', $data['pageInfo']);
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('hasPreviousPage', $data['pageInfo']);

        if (! empty($data['edges'])) {
            foreach ($data['edges'] as $edge) {
                $node = $edge['node'] ?? null;
                $this->assertNotNull($node, 'edge.node is null');
                $this->assertArrayHasKey('id', $node);
                $this->assertArrayHasKey('_id', $node);
                $this->assertArrayHasKey('code', $node);
                $this->assertArrayHasKey('timezone', $node);
                $this->assertArrayHasKey('theme', $node);
                $this->assertArrayHasKey('hostname', $node);
                $this->assertArrayHasKey('logo', $node);
                $this->assertArrayHasKey('favicon', $node);
                $this->assertArrayHasKey('isMaintenanceOn', $node);
                $this->assertArrayHasKey('allowedIps', $node);
                $this->assertArrayHasKey('createdAt', $node);
                $this->assertArrayHasKey('updatedAt', $node);
                $this->assertArrayHasKey('logoUrl', $node);
                $this->assertArrayHasKey('faviconUrl', $node);

                $this->assertArrayHasKey('translation', $node);
                $this->assertArrayHasKey('translations', $node);

                $this->assertIsArray($node['translations']['edges'] ?? []);
                $this->assertArrayHasKey('pageInfo', $node['translations']);
                $this->assertArrayHasKey('totalCount', $node['translations']);

                foreach ($node['translations']['edges'] ?? [] as $tEdge) {
                    $t = $tEdge['node'] ?? null;
                    $this->assertNotNull($t, 'translation node is null');
                    $this->assertArrayHasKey('id', $t);
                    $this->assertArrayHasKey('_id', $t);
                    $this->assertArrayHasKey('channelId', $t);
                    $this->assertArrayHasKey('locale', $t);
                    $this->assertArrayHasKey('name', $t);
                    $this->assertArrayHasKey('description', $t);
                }
            }
        }
    }

    /**
     * Get Channels with Pagination
     */
    public function test_get_channels_pagination_basic(): void
    {
        $query = <<<'GQL'
            query getChannels($first: Int, $after: String) {
              channels(first: $first, after: $after) {
                edges {
                  node {
                    id
                    _id
                    code
                    hostname
                    translation {
                      name
                      description
                    }
                    logoUrl
                  }
                  cursor
                }
                pageInfo {
                  endCursor
                  startCursor
                  hasNextPage
                  hasPreviousPage
                }
                totalCount
              }
            }
        GQL;

        $variables = ['first' => 10, 'after' => null];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $data = $response->json('data.channels');

        $this->assertNotNull($data, 'channels response is null');
        $this->assertIsArray($data['edges'] ?? [], 'channels.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        $this->assertArrayHasKey('endCursor', $data['pageInfo']);
        $this->assertArrayHasKey('startCursor', $data['pageInfo']);
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('hasPreviousPage', $data['pageInfo']);

        if (! empty($data['edges'])) {
            $firstEdge = $data['edges'][0] ?? null;
            $this->assertNotNull($firstEdge, 'first edge is null');
            $this->assertArrayHasKey('node', $firstEdge);
            $this->assertArrayHasKey('cursor', $firstEdge);

            $node = $firstEdge['node'] ?? null;
            $this->assertNotNull($node, 'edge.node is null');
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('_id', $node);
            $this->assertArrayHasKey('code', $node);
            $this->assertArrayHasKey('hostname', $node);
            $this->assertArrayHasKey('translation', $node);
            $this->assertArrayHasKey('logoUrl', $node);

            $trans = $node['translation'] ?? null;
            if ($trans) {
                $this->assertArrayHasKey('name', $trans);
                $this->assertArrayHasKey('description', $trans);
            }
        }
    }

    /**
     * Get Channels with All Translations
     */
    public function test_get_channels_all_translations_basic(): void
    {
        $query = <<<'GQL'
            query getChannels {
              channels {
                edges {
                  node {
                    id
                    _id
                    code
                    hostname
                    timezone
                    translations {
                      edges {
                        node {
                          id
                          locale
                          name
                          description
                          maintenanceModeText
                        }
                      }
                      totalCount
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.channels');

        $this->assertNotNull($data, 'channels response is null');
        $this->assertIsArray($data['edges'] ?? [], 'channels.edges is not an array');
        $this->assertArrayHasKey('totalCount', $data);

        if (! empty($data['edges'])) {
            foreach ($data['edges'] as $edge) {
                $node = $edge['node'] ?? null;
                $this->assertNotNull($node, 'edge.node is null');
                $this->assertArrayHasKey('id', $node);
                $this->assertArrayHasKey('_id', $node);
                $this->assertArrayHasKey('code', $node);
                $this->assertArrayHasKey('hostname', $node);
                $this->assertArrayHasKey('timezone', $node);
                $this->assertArrayHasKey('translations', $node);

                $this->assertIsArray($node['translations']['edges'] ?? []);
                $this->assertArrayHasKey('totalCount', $node['translations']);

                foreach ($node['translations']['edges'] ?? [] as $tEdge) {
                    $t = $tEdge['node'] ?? null;
                    $this->assertNotNull($t, 'translation node is null');
                    $this->assertArrayHasKey('id', $t);
                    $this->assertArrayHasKey('locale', $t);
                    $this->assertArrayHasKey('name', $t);
                    $this->assertArrayHasKey('description', $t);
                    $this->assertArrayHasKey('maintenanceModeText', $t);
                }
            }
        }
    }

    /**
     * Get Channels with Maintenance Mode Info
     */
    public function test_get_channels_maintenance_mode_info(): void
    {
        $query = <<<'GQL'
            query getChannels {
              channels {
                edges {
                  node {
                    id
                    _id
                    code
                    hostname
                    isMaintenanceOn
                    allowedIps
                    translation {
                      name
                      maintenanceModeText
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.channels');

        $this->assertNotNull($data, 'channels response is null');
        $this->assertIsArray($data['edges'] ?? [], 'channels.edges is not an array');
        $this->assertArrayHasKey('totalCount', $data);

        if (! empty($data['edges'])) {
            foreach ($data['edges'] as $edge) {
                $node = $edge['node'] ?? null;
                $this->assertNotNull($node, 'edge.node is null');
                $this->assertArrayHasKey('id', $node);
                $this->assertArrayHasKey('_id', $node);
                $this->assertArrayHasKey('code', $node);
                $this->assertArrayHasKey('hostname', $node);
                $this->assertArrayHasKey('isMaintenanceOn', $node);
                $this->assertArrayHasKey('allowedIps', $node);
                $this->assertArrayHasKey('translation', $node);

                $trans = $node['translation'] ?? null;
                if ($trans) {
                    $this->assertArrayHasKey('name', $trans);
                    $this->assertArrayHasKey('maintenanceModeText', $trans);
                }
            }
        }
    }

    /**
     * Get Channel By ID - Basic
     */
    public function test_get_channel_by_id_basic(): void
    {
        $query = <<<'GQL'
            query getChannelByID($id: ID!) {
              channel(id: $id) {
                id
                _id
                code
                hostname
                timezone
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/channels/1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.channel');

        $this->assertNotNull($node, 'channel response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('hostname', $node);
        $this->assertArrayHasKey('timezone', $node);
    }

    /**
     * Get Channel Complete Details
     */
    public function test_get_channel_complete_details(): void
    {
        $query = <<<'GQL'
            query getChannelByID($id: ID!) {
              channel(id: $id) {
                id
                _id
                code
                timezone
                theme
                hostname
                logo
                favicon
                isMaintenanceOn
                allowedIps
                createdAt
                updatedAt
                logoUrl
                faviconUrl
                translation {
                  id
                  _id
                  channelId
                  locale
                  name
                  description
                  maintenanceModeText
                  createdAt
                  updatedAt
                }
                translations {
                  edges {
                    node {
                      id
                      _id
                      channelId
                      locale
                      name
                      description
                      maintenanceModeText
                      createdAt
                      updatedAt
                    }
                    cursor
                  }
                  pageInfo {
                    endCursor
                    startCursor
                    hasNextPage
                    hasPreviousPage
                  }
                  totalCount
                }
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/channels/1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.channel');

        $this->assertNotNull($node, 'channel response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('timezone', $node);
        $this->assertArrayHasKey('theme', $node);
        $this->assertArrayHasKey('hostname', $node);
        $this->assertArrayHasKey('logo', $node);
        $this->assertArrayHasKey('favicon', $node);
        $this->assertArrayHasKey('isMaintenanceOn', $node);
        $this->assertArrayHasKey('allowedIps', $node);
        $this->assertArrayHasKey('createdAt', $node);
        $this->assertArrayHasKey('updatedAt', $node);
        $this->assertArrayHasKey('logoUrl', $node);
        $this->assertArrayHasKey('faviconUrl', $node);

        // Verify translation (singular)
        $this->assertArrayHasKey('translation', $node);
        $translation = $node['translation'];
        if ($translation) {
            $this->assertArrayHasKey('id', $translation);
            $this->assertArrayHasKey('_id', $translation);
            $this->assertArrayHasKey('channelId', $translation);
            $this->assertArrayHasKey('locale', $translation);
            $this->assertArrayHasKey('name', $translation);
            $this->assertArrayHasKey('description', $translation);
            $this->assertArrayHasKey('maintenanceModeText', $translation);
            $this->assertArrayHasKey('createdAt', $translation);
            $this->assertArrayHasKey('updatedAt', $translation);
        }

        // Verify translations (plural - paginated)
        $this->assertArrayHasKey('translations', $node);
        $translations = $node['translations'];
        $this->assertIsArray($translations['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $translations);
        $this->assertArrayHasKey('totalCount', $translations);

        // pageInfo assertions
        $this->assertArrayHasKey('endCursor', $translations['pageInfo']);
        $this->assertArrayHasKey('startCursor', $translations['pageInfo']);
        $this->assertArrayHasKey('hasNextPage', $translations['pageInfo']);
        $this->assertArrayHasKey('hasPreviousPage', $translations['pageInfo']);

        // Verify translation edges
        foreach ($translations['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $this->assertArrayHasKey('cursor', $edge);

            $t = $edge['node'] ?? null;
            $this->assertNotNull($t, 'translation node is null');
            $this->assertArrayHasKey('id', $t);
            $this->assertArrayHasKey('_id', $t);
            $this->assertArrayHasKey('channelId', $t);
            $this->assertArrayHasKey('locale', $t);
            $this->assertArrayHasKey('name', $t);
            $this->assertArrayHasKey('description', $t);
            $this->assertArrayHasKey('maintenanceModeText', $t);
            $this->assertArrayHasKey('createdAt', $t);
            $this->assertArrayHasKey('updatedAt', $t);
        }
    }

    /**
     * Get Channel with Branding Assets
     */
    public function test_get_channel_with_branding_assets(): void
    {
        $query = <<<'GQL'
            query getChannelByID($id: ID!) {
              channel(id: $id) {
                id
                _id
                code
                hostname
                theme
                logo
                favicon
                logoUrl
                faviconUrl
                translation {
                  name
                  description
                }
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/channels/1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.channel');

        $this->assertNotNull($node, 'channel response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('hostname', $node);
        $this->assertArrayHasKey('theme', $node);
        $this->assertArrayHasKey('logo', $node);
        $this->assertArrayHasKey('favicon', $node);
        $this->assertArrayHasKey('logoUrl', $node);
        $this->assertArrayHasKey('faviconUrl', $node);

        // Verify translation
        $this->assertArrayHasKey('translation', $node);
        $translation = $node['translation'];
        if ($translation) {
            $this->assertArrayHasKey('name', $translation);
            $this->assertArrayHasKey('description', $translation);
        }
    }

    /**
     * Get Channel Maintenance Mode details
     */
    public function test_get_channel_maintenance_mode_details(): void
    {
        $query = <<<'GQL'
            query getChannelByID($id: ID!) {
              channel(id: $id) {
                id
                _id
                code
                hostname
                isMaintenanceOn
                allowedIps
                translation {
                  locale
                  name
                  maintenanceModeText
                }
                translations {
                  edges {
                    node {
                      locale
                      maintenanceModeText
                    }
                  }
                  totalCount
                }
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/channels/1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.channel');

        $this->assertNotNull($node, 'channel response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('hostname', $node);
        $this->assertArrayHasKey('isMaintenanceOn', $node);
        $this->assertArrayHasKey('allowedIps', $node);

        // Verify translation (singular)
        $this->assertArrayHasKey('translation', $node);
        $translation = $node['translation'];
        if ($translation) {
            $this->assertArrayHasKey('locale', $translation);
            $this->assertArrayHasKey('name', $translation);
            $this->assertArrayHasKey('maintenanceModeText', $translation);
        }

        // Verify translations (plural)
        $this->assertArrayHasKey('translations', $node);
        $translations = $node['translations'];
        $this->assertIsArray($translations['edges'] ?? []);
        $this->assertArrayHasKey('totalCount', $translations);

        // Verify translation edges
        foreach ($translations['edges'] ?? [] as $edge) {
            $t = $edge['node'] ?? null;
            $this->assertNotNull($t, 'translation node is null');
            $this->assertArrayHasKey('locale', $t);
            $this->assertArrayHasKey('maintenanceModeText', $t);
        }
    }

    /**
     * Get Channel By ID with locales, currencies, defaultLocale, and baseCurrency relations.
     */
    public function test_get_channel_with_locales_currencies_and_defaults(): void
    {
        $query = <<<'GQL'
            query getChannelByID($id: ID!) {
              channel(id: $id) {
                id
                _id
                code
                hostname
                theme
                timezone
                homeSeo
                logoUrl
                faviconUrl
                locales {
                  edges { node { id, _id, code, name, direction } }
                }
                currencies {
                  edges { node { id, _id, code, name, symbol } }
                }
                defaultLocale {
                  id, _id, code, name, direction
                }
                baseCurrency {
                  id, _id, code, name, symbol
                }
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/channels/1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.channel');

        $this->assertNotNull($node, 'channel response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('hostname', $node);
        $this->assertArrayHasKey('theme', $node);
        $this->assertArrayHasKey('timezone', $node);
        $this->assertArrayHasKey('homeSeo', $node);
        $this->assertArrayHasKey('logoUrl', $node);
        $this->assertArrayHasKey('faviconUrl', $node);

        // Locales (BelongsToMany)
        $this->assertArrayHasKey('locales', $node);
        $localeEdges = $node['locales']['edges'] ?? [];
        $this->assertNotEmpty($localeEdges, 'Channel should have at least one locale');
        foreach ($localeEdges as $edge) {
            $locale = $edge['node'] ?? null;
            $this->assertNotNull($locale);
            $this->assertArrayHasKey('id', $locale);
            $this->assertArrayHasKey('_id', $locale);
            $this->assertArrayHasKey('code', $locale);
            $this->assertArrayHasKey('name', $locale);
            $this->assertArrayHasKey('direction', $locale);
        }

        // Currencies (BelongsToMany)
        $this->assertArrayHasKey('currencies', $node);
        $currencyEdges = $node['currencies']['edges'] ?? [];
        $this->assertNotEmpty($currencyEdges, 'Channel should have at least one currency');
        foreach ($currencyEdges as $edge) {
            $currency = $edge['node'] ?? null;
            $this->assertNotNull($currency);
            $this->assertArrayHasKey('id', $currency);
            $this->assertArrayHasKey('_id', $currency);
            $this->assertArrayHasKey('code', $currency);
            $this->assertArrayHasKey('name', $currency);
            $this->assertArrayHasKey('symbol', $currency);
        }

        // Default Locale (BelongsTo)
        $this->assertArrayHasKey('defaultLocale', $node);
        $defaultLocale = $node['defaultLocale'];
        $this->assertNotNull($defaultLocale, 'Channel should have a default locale');
        $this->assertArrayHasKey('id', $defaultLocale);
        $this->assertArrayHasKey('_id', $defaultLocale);
        $this->assertArrayHasKey('code', $defaultLocale);
        $this->assertArrayHasKey('name', $defaultLocale);
        $this->assertArrayHasKey('direction', $defaultLocale);

        // Base Currency (BelongsTo)
        $this->assertArrayHasKey('baseCurrency', $node);
        $baseCurrency = $node['baseCurrency'];
        $this->assertNotNull($baseCurrency, 'Channel should have a base currency');
        $this->assertArrayHasKey('id', $baseCurrency);
        $this->assertArrayHasKey('_id', $baseCurrency);
        $this->assertArrayHasKey('code', $baseCurrency);
        $this->assertArrayHasKey('name', $baseCurrency);
        $this->assertArrayHasKey('symbol', $baseCurrency);
    }

    /**
     * Get Channel with all Translations
     */
    public function test_get_channel_with_all_translations(): void
    {
        $query = <<<'GQL'
            query getChannelByID($id: ID!) {
              channel(id: $id) {
                id
                _id
                code
                hostname
                timezone
                translations {
                  edges {
                    node {
                      id
                      locale
                      name
                      description
                      maintenanceModeText
                    }
                    cursor
                  }
                  pageInfo {
                    hasNextPage
                    endCursor
                  }
                  totalCount
                }
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/channels/1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.channel');

        $this->assertNotNull($node, 'channel response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('hostname', $node);
        $this->assertArrayHasKey('timezone', $node);

        // Verify translations (plural)
        $this->assertArrayHasKey('translations', $node);
        $translations = $node['translations'];
        $this->assertIsArray($translations['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $translations);
        $this->assertArrayHasKey('totalCount', $translations);

        // pageInfo assertions
        $this->assertArrayHasKey('hasNextPage', $translations['pageInfo']);
        $this->assertArrayHasKey('endCursor', $translations['pageInfo']);

        // Verify translation edges
        foreach ($translations['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $this->assertArrayHasKey('cursor', $edge);

            $t = $edge['node'] ?? null;
            $this->assertNotNull($t, 'translation node is null');
            $this->assertArrayHasKey('id', $t);
            $this->assertArrayHasKey('locale', $t);
            $this->assertArrayHasKey('name', $t);
            $this->assertArrayHasKey('description', $t);
            $this->assertArrayHasKey('maintenanceModeText', $t);
        }
    }
}
