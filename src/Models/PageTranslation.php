<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Webkul\CMS\Models\PageTranslation as BasePageTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 10,
            paginationMaximumItemsPerPage: 100,
            paginationClientItemsPerPage: true,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CMS Page Translation'],
                summary: 'List CMS page translations',
            ),
        ),
        new Get(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CMS Page Translation'],
                summary: 'Get a single CMS page translation by ID',
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
