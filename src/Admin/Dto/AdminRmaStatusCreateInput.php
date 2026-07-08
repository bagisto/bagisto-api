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

    public function setTitle(?string $v): void
    {
        $this->title = $v;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $v): void
    {
        $this->status = $v;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $v): void
    {
        $this->color = $v;
    }
}
