<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaStatusUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'RMA status IRI, e.g. /api/admin/rma/statuses/3')]
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
     * The color.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $color = null;

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
     * Get color.
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Set color.
     */
    public function setColor(?string $value): void
    {
        $this->color = $value;
    }
}
