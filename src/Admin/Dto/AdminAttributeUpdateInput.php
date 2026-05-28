<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/catalog/attributes/{id}.
 *
 * Code changes are refused — field is accepted but triggers 422 if different.
 */
class AdminAttributeUpdateInput
{
    #[ApiProperty(description: 'Attribute ID (IRI, e.g. /api/admin/catalog/attributes/12).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Attribute code (must match the existing code — cannot be changed).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Admin display name.')]
    #[Groups(['mutation'])]
    public ?string $admin_name = null;

    #[ApiProperty(description: 'Attribute type.')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    #[ApiProperty(description: 'Swatch display type.')]
    #[Groups(['mutation'])]
    public ?string $swatch_type = null;

    #[ApiProperty(description: 'Whether the attribute is required.')]
    #[Groups(['mutation'])]
    public ?bool $is_required = null;

    #[ApiProperty(description: 'Whether the attribute value must be unique.')]
    #[Groups(['mutation'])]
    public ?bool $is_unique = null;

    #[ApiProperty(description: 'Whether the attribute is filterable.')]
    #[Groups(['mutation'])]
    public ?bool $is_filterable = null;

    #[ApiProperty(description: 'Whether the attribute is configurable (for variants).')]
    #[Groups(['mutation'])]
    public ?bool $is_configurable = null;

    #[ApiProperty(description: 'Whether the attribute is visible on the storefront.')]
    #[Groups(['mutation'])]
    public ?bool $is_visible_on_front = null;

    #[ApiProperty(description: 'Whether the attribute is comparable.')]
    #[Groups(['mutation'])]
    public ?bool $is_comparable = null;

    #[ApiProperty(description: 'Whether the attribute value varies per locale.')]
    #[Groups(['mutation'])]
    public ?bool $value_per_locale = null;

    #[ApiProperty(description: 'Whether the attribute value varies per channel.')]
    #[Groups(['mutation'])]
    public ?bool $value_per_channel = null;

    #[ApiProperty(description: 'Whether WYSIWYG editor is enabled.')]
    #[Groups(['mutation'])]
    public ?bool $enable_wysiwyg = null;

    #[ApiProperty(description: 'Validation rule.')]
    #[Groups(['mutation'])]
    public ?string $validation = null;

    #[ApiProperty(description: 'Custom regex pattern.')]
    #[Groups(['mutation'])]
    public ?string $regex = null;

    #[ApiProperty(description: 'Default value (0 or 1 for boolean type).')]
    #[Groups(['mutation'])]
    public ?string $default_value = null;

    #[ApiProperty(description: 'Sort position.')]
    #[Groups(['mutation'])]
    public ?int $position = null;

    /**
     * Locale-keyed translations map.
     *
     * @var array<string, array{name: string}>|null
     */
    #[ApiProperty(description: 'Locale-keyed translations map.')]
    #[Groups(['mutation'])]
    public ?array $translations = null;

    /**
     * Options array for replacement (select/multiselect/checkbox only).
     * Items with an "id" are updates; items without "id" are new; any existing
     * option not included is deleted.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty(description: 'Full replacement options array. Items with id are updated; without id are inserted; omitted ids are deleted.')]
    #[Groups(['mutation'])]
    public ?array $options = null;
}
