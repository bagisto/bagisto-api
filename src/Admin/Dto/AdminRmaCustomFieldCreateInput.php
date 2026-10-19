<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaCustomFieldCreateInput
{
    /**
     * The code.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The label.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $label = null;

    /**
     * The type.
     */
    #[ApiProperty(description: 'text | textarea | select | multiselect | checkbox | radio | date')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    /**
     * The is required.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $is_required = null;

    /**
     * The position.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $position = null;

    /**
     * The input validation.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $input_validation = null;

    /**
     * The status.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /** @var array<int,array{name:string,value:string}>|null */
    #[ApiProperty(description: 'Required for select/multiselect/checkbox/radio: [{ name, value }]')]
    #[Groups(['mutation'])]
    public ?array $options = null;

    /**
     * Get code.
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Set code.
     */
    public function setCode(?string $value): void
    {
        $this->code = $value;
    }

    /**
     * Get label.
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set label.
     */
    public function setLabel(?string $value): void
    {
        $this->label = $value;
    }

    /**
     * Get type.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set type.
     */
    public function setType(?string $value): void
    {
        $this->type = $value;
    }

    /**
     * Get is required.
     */
    public function getIs_required(): ?int
    {
        return $this->is_required;
    }

    /**
     * Set is required.
     */
    public function setIs_required(?int $value): void
    {
        $this->is_required = $value;
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
     * Get input validation.
     */
    public function getInput_validation(): ?string
    {
        return $this->input_validation;
    }

    /**
     * Set input validation.
     */
    public function setInput_validation(?string $value): void
    {
        $this->input_validation = $value;
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
     * Get options.
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * Set options.
     */
    public function setOptions(?array $value): void
    {
        $this->options = $value;
    }
}
