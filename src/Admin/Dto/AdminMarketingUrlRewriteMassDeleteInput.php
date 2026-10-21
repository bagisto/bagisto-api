<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingUrlRewriteMassDeleteInput
{
    /**
     * The ids of the URL rewrites to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of URL rewrite IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
