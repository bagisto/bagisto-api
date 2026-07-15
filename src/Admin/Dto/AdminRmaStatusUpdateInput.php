<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaStatusUpdateInput
{
    #[ApiProperty(description: 'RMA status IRI, e.g. /api/admin/rma/statuses/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $title = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $color = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $v): void
    {
        $this->id = $v;
    }

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
