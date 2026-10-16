<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaStatusCreateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $title = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $color = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $value): void
    {
        $this->title = $value;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $value): void
    {
        $this->status = $value;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $value): void
    {
        $this->color = $value;
    }
}
