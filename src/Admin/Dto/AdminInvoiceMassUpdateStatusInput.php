<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminInvoiceMassUpdateStatusInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of invoice ids to update.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    #[ApiProperty(description: 'New invoice status: pending, paid, or overdue.')]
    #[Groups(['mutation'])]
    public ?string $value = null;
}
