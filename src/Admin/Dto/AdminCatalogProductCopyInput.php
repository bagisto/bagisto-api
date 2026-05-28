<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for createAdminCatalogProductCopy (GraphQL) /
 * POST /api/admin/catalog/products/{sourceId}/copy (REST).
 *
 * REST reads sourceId from the URI; GraphQL reads it from this DTO.
 */
class AdminCatalogProductCopyInput
{
    #[ApiProperty(description: 'ID of the source product to copy.')]
    #[Groups(['mutation'])]
    public ?int $sourceId = null;
}
