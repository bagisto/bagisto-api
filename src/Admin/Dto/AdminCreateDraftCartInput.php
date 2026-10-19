<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCreateDraftCartInput
{
    /**
     * The customer id.
     */
    #[ApiProperty(description: 'Customer ID the draft cart will belong to.')]
    #[Groups(['mutation'])]
    public ?int $customerId = null;
}
