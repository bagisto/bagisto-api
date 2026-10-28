<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaReasonCreateInput
{
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

    /**
     * The resolutions the RMA reason allows: return, cancel_items or both.
     *
     * @var array<int,string>|null
     */
    #[ApiProperty(description: 'One or more of: return, cancel_items')]
    #[Groups(['mutation'])]
    public ?array $resolution_type = null;

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
