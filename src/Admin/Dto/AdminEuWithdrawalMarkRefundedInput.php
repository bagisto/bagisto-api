<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminEuWithdrawalMarkRefundedInput
{
    #[ApiProperty(description: 'Withdrawal IRI, e.g. /api/admin/eu-withdrawals/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Optional admin reference for the out-of-band refund (max 500).')]
    #[Groups(['mutation'])]
    public ?string $refund_note = null;

    public function getId(): ?string { return $this->id; }
    public function setId(?string $v): void { $this->id = $v; }
    public function getRefund_note(): ?string { return $this->refund_note; }
    public function setRefund_note(?string $v): void { $this->refund_note = $v; }
}
