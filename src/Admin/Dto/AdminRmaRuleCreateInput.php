<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaRuleCreateInput
{
    /**
     * The name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The status.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The return period.
     */
    #[ApiProperty(description: 'Return window in days')]
    #[Groups(['mutation'])]
    public ?int $return_period = null;

    /**
     * Get name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name.
     */
    public function setName(?string $value): void
    {
        $this->name = $value;
    }

    /**
     * Get description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set description.
     */
    public function setDescription(?string $value): void
    {
        $this->description = $value;
    }

    /**
     * Get status.
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Set status.
     */
    public function setStatus(?int $value): void
    {
        $this->status = $value;
    }

    /**
     * Get return period.
     */
    public function getReturn_period(): ?int
    {
        return $this->return_period;
    }

    /**
     * Set return period.
     */
    public function setReturn_period(?int $value): void
    {
        $this->return_period = $value;
    }
}
