<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminEuWithdrawalMarkRefundedInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Withdrawal IRI, e.g. /api/admin/eu-withdrawals/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The refund note.
     */
    #[ApiProperty(description: 'Optional admin reference for the out-of-band refund (max 500).')]
    #[Groups(['mutation'])]
    public ?string $refund_note = null;

    /**
     * Get id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set id.
     */
    public function setId(?string $value): void
    {
        $this->id = $value;
    }

    /**
     * Get refund note.
     */
    public function getRefund_note(): ?string
    {
        return $this->refund_note;
    }

    /**
     * Set refund note.
     */
    public function setRefund_note(?string $value): void
    {
        $this->refund_note = $value;
    }
}
