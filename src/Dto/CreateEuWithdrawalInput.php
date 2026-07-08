<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateEuWithdrawalInput
{
    #[ApiProperty(description: 'Order id the authenticated customer is withdrawing from.')]
    #[Groups(['mutation'])]
    public ?int $order_id = null;

    #[ApiProperty(description: 'Optional free-text reason (max 5000 chars).')]
    #[Groups(['mutation'])]
    public ?string $reason_text = null;

    public function getOrder_id(): ?int { return $this->order_id; }
    public function setOrder_id(?int $v): void { $this->order_id = $v; }
    public function getReason_text(): ?string { return $this->reason_text; }
    public function setReason_text(?string $v): void { $this->reason_text = $v; }
}
