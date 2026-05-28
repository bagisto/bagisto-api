<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSearchTermUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/search-terms/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Search term text.')]
    #[Groups(['mutation'])]
    public ?string $term = null;

    #[ApiProperty(description: 'Optional redirect URL.')]
    #[Groups(['mutation'])]
    public ?string $redirect_url = null;
}
