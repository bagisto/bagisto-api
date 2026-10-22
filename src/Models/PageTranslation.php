<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\CMS\Models\PageTranslation as BasePageTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 10,
            paginationMaximumItemsPerPage: 100,
            paginationClientItemsPerPage: true,
            openapi: new Operation(
                tags: ['CMS Page Translation'],
                summary: 'List CMS page translations',
                description: 'Returns per-locale CMS page translations. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'List of CMS page translations.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'pageTitle' => 'About Us (Updated)',
                                        'urlKey' => 'about-us',
                                        'htmlContent' => '<h1>About Us</h1>',
                                        'metaTitle' => 'about us',
                                        'metaDescription' => '',
                                        'metaKeywords' => 'aboutus',
                                        'locale' => 'en',
                                        'cmsPageId' => 1,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            openapi: new Operation(
                tags: ['CMS Page Translation'],
                summary: 'Get a single CMS page translation by ID',
                description: 'Returns one CMS page translation. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The CMS page translation.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'pageTitle' => 'About Us (Updated)',
                                    'urlKey' => 'about-us',
                                    'htmlContent' => '<h1>About Us</h1>',
                                    'metaTitle' => 'about us',
                                    'metaDescription' => '',
                                    'metaKeywords' => 'aboutus',
                                    'locale' => 'en',
                                    'cmsPageId' => 1,
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Translation not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CursorAwareCollectionProvider::class,
            description: 'Every CMS page translation (one row per page and locale), ordered by ID. Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 10 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header.',
        ),
        new Query(
            resolver: BaseQueryItemResolver::class,
            description: 'One CMS page translation. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header.',
        ),
    ],
)]
class PageTranslation extends BasePageTranslation
{
    /**
     * Get unique translation identifier.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
