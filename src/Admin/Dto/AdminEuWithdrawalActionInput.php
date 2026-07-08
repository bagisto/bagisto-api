<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminEuWithdrawalActionInput
{
    #[ApiProperty(description: 'Withdrawal IRI, e.g. /api/admin/eu-withdrawals/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $v): void
    {
        $this->id = $v;
    }
}
