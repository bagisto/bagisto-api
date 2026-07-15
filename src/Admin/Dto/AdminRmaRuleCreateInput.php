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

    public function setName(?string $v): void
    {
        $this->name = $v;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $v): void
    {
        $this->description = $v;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $v): void
    {
        $this->status = $v;
    }

    public function getReturn_period(): ?int
    {
        return $this->return_period;
    }

    public function setReturn_period(?int $v): void
    {
        $this->return_period = $v;
    }
}
