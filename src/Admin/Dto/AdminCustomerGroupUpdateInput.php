<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerGroupUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/customers/groups/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The code.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $name = null;
}
