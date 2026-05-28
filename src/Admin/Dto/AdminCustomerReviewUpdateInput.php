<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerReviewUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/customers/reviews/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'New status: pending|approved|disapproved.')]
    #[Groups(['mutation'])]
    public ?string $status = null;
}
