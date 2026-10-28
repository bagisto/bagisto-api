<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductCopyInput
{
    /**
     * The source id.
     */
    #[ApiProperty(description: 'ID of the source product to copy.')]
    #[Groups(['mutation'])]
    public ?int $sourceId = null;
}
