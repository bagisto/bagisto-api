<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
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
    ]
)]
class PageTranslation extends BasePageTranslation
{
    /**
     * Get unique translation identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
