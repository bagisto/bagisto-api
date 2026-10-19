<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminEuWithdrawalActionInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Withdrawal IRI, e.g. /api/admin/eu-withdrawals/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

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
}
