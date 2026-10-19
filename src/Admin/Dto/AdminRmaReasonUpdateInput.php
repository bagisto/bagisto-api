<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaReasonUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'RMA reason IRI, e.g. /api/admin/rma/reasons/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The title.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $title = null;

    /**
     * The status.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The position.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $position = null;

    /** @var array<int,string>|null */
    #[ApiProperty(description: 'One or more of: return, cancel_items')]
    #[Groups(['mutation'])]
    public ?array $resolution_type = null;

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
     * Get title.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set title.
     */
    public function setTitle(?string $value): void
    {
        $this->title = $value;
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
     * Get position.
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * Set position.
     */
    public function setPosition(?int $value): void
    {
        $this->position = $value;
    }

    /**
     * Get resolution type.
     */
    public function getResolution_type(): ?array
    {
        return $this->resolution_type;
    }

    /**
     * Set resolution type.
     */
    public function setResolution_type(?array $value): void
    {
        $this->resolution_type = $value;
    }
}
