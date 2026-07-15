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
                                    'indexFile' => '/sitemap.xml',
                                    'generatedSitemaps' => ['/sitemap-products-1.xml', '/sitemap-categories-1.xml'],
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

    #[ApiProperty(writable: false, description: 'Generated sitemap index file path under the public disk.')]
    public ?string $index_file = null;

    #[ApiProperty(writable: false, description: 'Generated child sitemap file paths.')]
    public ?array $generated_sitemaps = null;

    #[ApiProperty(writable: false)]
    public ?string $generated_at = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
