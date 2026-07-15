<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaReasonUpdateInput
{
    #[ApiProperty(description: 'RMA reason IRI, e.g. /api/admin/rma/reasons/3')]
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
    public ?int $position = null;

    /** @var array<int,string>|null */
    #[ApiProperty(description: 'One or more of: return, cancel_items')]
    #[Groups(['mutation'])]
    public ?array $resolution_type = null;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $v): void
    {
        $this->position = $v;
    }

    public function getResolution_type(): ?array
    {
        return $this->resolution_type;
    }

    public function setResolution_type(?array $v): void
    {
        $this->resolution_type = $v;
    }
}
