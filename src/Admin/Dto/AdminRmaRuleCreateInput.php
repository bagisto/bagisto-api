<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaRuleCreateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty(description: 'Return window in days')]
    #[Groups(['mutation'])]
    public ?int $return_period = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $value): void
    {
        $this->name = $value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $value): void
    {
        $this->description = $value;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $value): void
    {
        $this->status = $value;
    }

    public function getReturn_period(): ?int
    {
        return $this->return_period;
    }

    public function setReturn_period(?int $value): void
    {
        $this->return_period = $value;
    }
}
