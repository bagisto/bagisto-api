<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateGuestEuWithdrawalInput
{
    /**
     * The order increment id.
     */
    #[ApiProperty(description: 'Order increment id (e.g. "1000123").')]
    #[Groups(['mutation'])]
    public ?string $order_increment_id = null;

    /**
     * The email.
     */
    #[ApiProperty(description: 'Email on the guest order — proves ownership.')]
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * The reason text.
     */
    #[ApiProperty(description: 'Optional free-text reason (max 5000 chars).')]
    #[Groups(['mutation'])]
    public ?string $reason_text = null;

    /**
     * Get order increment id.
     */
    public function getOrder_increment_id(): ?string
    {
        return $this->order_increment_id;
    }

    /**
     * Set order increment id.
     */
    public function setOrder_increment_id(?string $value): void
    {
        $this->order_increment_id = $value;
    }

    /**
     * Get email.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set email.
     */
    public function setEmail(?string $value): void
    {
        $this->email = $value;
    }

    /**
     * Get reason text.
     */
    public function getReason_text(): ?string
    {
        return $this->reason_text;
    }

    /**
     * Set reason text.
     */
    public function setReason_text(?string $value): void
    {
        $this->reason_text = $value;
    }
}
