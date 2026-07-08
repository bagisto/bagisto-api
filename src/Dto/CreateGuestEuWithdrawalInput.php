<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateGuestEuWithdrawalInput
{
    #[ApiProperty(description: 'Order increment id (e.g. "1000123").')]
    #[Groups(['mutation'])]
    public ?string $order_increment_id = null;

    #[ApiProperty(description: 'Email on the guest order — proves ownership.')]
    #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty(description: 'Optional free-text reason (max 5000 chars).')]
    #[Groups(['mutation'])]
    public ?string $reason_text = null;

    public function getOrder_increment_id(): ?string { return $this->order_increment_id; }
    public function setOrder_increment_id(?string $v): void { $this->order_increment_id = $v; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $v): void { $this->email = $v; }
    public function getReason_text(): ?string { return $this->reason_text; }
    public function setReason_text(?string $v): void { $this->reason_text = $v; }
}
