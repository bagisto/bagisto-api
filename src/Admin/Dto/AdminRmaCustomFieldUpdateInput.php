<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaCustomFieldUpdateInput
{
    #[ApiProperty(description: 'RMA custom field IRI, e.g. /api/admin/rma/custom-fields/3')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $label = null;

    #[ApiProperty(description: 'text | textarea | select | multiselect | checkbox | radio | date')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $is_required = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $position = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $input_validation = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /** @var array<int,array{name:string,value:string}>|null */
    #[ApiProperty(description: 'Required for select/multiselect/checkbox/radio: [{ name, value }]')]
    #[Groups(['mutation'])]
    public ?array $options = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $value): void
    {
        $this->id = $value;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $value): void
    {
        $this->code = $value;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $value): void
    {
        $this->label = $value;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $value): void
    {
        $this->type = $value;
    }

    public function getIs_required(): ?int
    {
        return $this->is_required;
    }

    public function setIs_required(?int $value): void
    {
        $this->is_required = $value;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $value): void
    {
        $this->position = $value;
    }

    public function getInput_validation(): ?string
    {
        return $this->input_validation;
    }

    public function setInput_validation(?string $value): void
    {
        $this->input_validation = $value;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $value): void
    {
        $this->status = $value;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $value): void
    {
        $this->options = $value;
    }
}
