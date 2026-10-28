<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSearchSynonymCreateInput
{
    /**
     * The name.
     */
    #[ApiProperty(description: 'Synonym group display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The terms.
     */
    #[ApiProperty(description: 'Comma-separated list of synonym terms (e.g. "shirt,tshirt,tee").')]
    #[Groups(['mutation'])]
    public ?string $terms = null;
}
