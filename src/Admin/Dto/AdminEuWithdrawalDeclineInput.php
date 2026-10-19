<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminEuWithdrawalDeclineInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Withdrawal IRI, e.g. /api/admin/eu-withdrawals/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The declined reason.
     */
    #[ApiProperty(description: 'Reason the merchant contests entitlement (max 500).')]
    #[Groups(['mutation'])]
    public ?string $declined_reason = null;

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
     * Get declined reason.
     */
    public function getDeclined_reason(): ?string
    {
        return $this->declined_reason;
    }

    /**
     * Set declined reason.
     */
    public function setDeclined_reason(?string $value): void
    {
        $this->declined_reason = $value;
    }
}
