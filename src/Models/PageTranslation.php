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
        new GetCollection,
        new Get,
    ]
)]
class PageTranslation extends BasePageTranslation
{
    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
