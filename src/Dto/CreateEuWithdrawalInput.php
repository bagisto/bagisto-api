<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateEuWithdrawalInput
{
    /**
     * The order id.
     */
    #[ApiProperty(description: 'Order id the authenticated customer is withdrawing from.')]
    #[Groups(['mutation'])]
    public ?int $order_id = null;

    /**
     * The reason text.
     */
    #[ApiProperty(description: 'Optional free-text reason (max 5000 chars).')]
    #[Groups(['mutation'])]
    public ?string $reason_text = null;

    /**
     * Get order id.
     */
    public function getOrder_id(): ?int
    {
        return $this->order_id;
    }

    /**
     * Set order id.
     */
    public function setOrder_id(?int $value): void
    {
        $this->order_id = $value;
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
