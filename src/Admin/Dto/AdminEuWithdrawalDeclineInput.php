<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminEuWithdrawalDeclineInput
{
    #[ApiProperty(description: 'Withdrawal IRI, e.g. /api/admin/eu-withdrawals/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Reason the merchant contests entitlement (max 500).')]
    #[Groups(['mutation'])]
    public ?string $declined_reason = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $v): void
    {
        $this->id = $v;
    }

    public function getDeclined_reason(): ?string
    {
        return $this->declined_reason;
    }

    public function setDeclined_reason(?string $v): void
    {
        $this->declined_reason = $v;
    }
}
