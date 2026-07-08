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

    public function getId(): ?string { return $this->id; }
    public function setId(?string $v): void { $this->id = $v; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(?string $v): void { $this->code = $v; }
    public function getLabel(): ?string { return $this->label; }
    public function setLabel(?string $v): void { $this->label = $v; }
    public function getType(): ?string { return $this->type; }
    public function setType(?string $v): void { $this->type = $v; }
    public function getIs_required(): ?int { return $this->is_required; }
    public function setIs_required(?int $v): void { $this->is_required = $v; }
    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $v): void { $this->position = $v; }
    public function getInput_validation(): ?string { return $this->input_validation; }
    public function setInput_validation(?string $v): void { $this->input_validation = $v; }
    public function getStatus(): ?int { return $this->status; }
    public function setStatus(?int $v): void { $this->status = $v; }
    public function getOptions(): ?array { return $this->options; }
    public function setOptions(?array $v): void { $this->options = $v; }
}
