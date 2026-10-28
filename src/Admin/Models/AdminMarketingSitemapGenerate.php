<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSitemapGenerateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapGenerateProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingSitemapGenerate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/sitemaps/{id}/generate',
            input: AdminMarketingSitemapGenerateInput::class,
            processor: AdminMarketingSitemapGenerateProcessor::class,
            status: 200,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Regenerate a sitemap',
                description: 'Walks every public Category / Product / Page and (re)writes the XML files under the public disk.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Sitemap regenerated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'sitemapId' => 1,
                                    'generatedFiles' => [
                                        [
                                            'channelId' => 1,
                                            'channelCode' => 'default',
                                            'hostname' => 'https://example.com',
                                            'index' => 'sitemaps/default/sitemap-1-1.xml',
                                            'sitemaps' => ['sitemaps/default/sitemap-1-1-1.xml'],
                                        ],
                                    ],
                                    'urls' => ['https://example.com/storage/sitemaps/default/sitemap-1-1.xml'],
                                    'indexFile' => null,
                                    'generatedSitemaps' => [],
                                    'generatedAt' => '2026-06-23T13:00:00+05:30',
                                    'message' => 'Sitemap generated.',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks marketing.search_seo.sitemaps.edit.'),
                    '404' => new Model\Response(description: 'Sitemap not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminMarketingSitemapGenerateInput::class,
            processor: AdminMarketingSitemapGenerateProcessor::class,
            description: 'Regenerate a sitemap. Becomes createAdminMarketingSitemapGenerate.',
        ),
    ],
)]
class AdminMarketingSitemapGenerate
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $sitemap_id = null;

    #[ApiProperty(writable: false, description: 'What the run wrote, one entry per covered channel — { channelId, channelCode, hostname, index, sitemaps }.')]
    public ?array $generated_files = null;

    #[ApiProperty(writable: false, description: 'Public index URL per channel — the link to submit to a search engine.')]
    public ?array $urls = null;

    #[ApiProperty(writable: false, description: 'Legacy: index path of a sitemap generated before generation became channel-aware. Null for anything generated since; read generatedFiles instead.')]
    public ?string $index_file = null;

    #[ApiProperty(writable: false, description: 'Generated child sitemap file paths.')]
    public ?array $generated_sitemaps = null;

    #[ApiProperty(writable: false)]
    public ?string $generated_at = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
