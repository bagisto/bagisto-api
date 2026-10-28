<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAttributeUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Attribute ID (IRI, e.g. /api/admin/catalog/attributes/12).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The code.
     */
    #[ApiProperty(description: 'Attribute code (must match the existing code — cannot be changed).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The admin name.
     */
    #[ApiProperty(description: 'Admin display name.')]
    #[Groups(['mutation'])]
    public ?string $admin_name = null;

    /**
     * The type.
     */
    #[ApiProperty(description: 'Attribute type.')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    /**
     * The swatch type.
     */
    #[ApiProperty(description: 'Swatch display type.')]
    #[Groups(['mutation'])]
    public ?string $swatch_type = null;

    /**
     * The is required.
     */
    #[ApiProperty(description: 'Whether the attribute is required.')]
    #[Groups(['mutation'])]
    public ?bool $is_required = null;

    /**
     * The is unique.
     */
    #[ApiProperty(description: 'Whether the attribute value must be unique.')]
    #[Groups(['mutation'])]
    public ?bool $is_unique = null;

    /**
     * The is filterable.
     */
    #[ApiProperty(description: 'Whether the attribute is filterable.')]
    #[Groups(['mutation'])]
    public ?bool $is_filterable = null;

    /**
     * The is configurable.
     */
    #[ApiProperty(description: 'Whether the attribute is configurable (for variants).')]
    #[Groups(['mutation'])]
    public ?bool $is_configurable = null;

    /**
     * The is visible on front.
     */
    #[ApiProperty(description: 'Whether the attribute is visible on the storefront.')]
    #[Groups(['mutation'])]
    public ?bool $is_visible_on_front = null;

    /**
     * The is comparable.
     */
    #[ApiProperty(description: 'Whether the attribute is comparable.')]
    #[Groups(['mutation'])]
    public ?bool $is_comparable = null;

    /**
     * The value per locale.
     */
    #[ApiProperty(description: 'Whether the attribute value varies per locale.')]
    #[Groups(['mutation'])]
    public ?bool $value_per_locale = null;

    /**
     * The value per channel.
     */
    #[ApiProperty(description: 'Whether the attribute value varies per channel.')]
    #[Groups(['mutation'])]
    public ?bool $value_per_channel = null;

    /**
     * The enable wysiwyg.
     */
    #[ApiProperty(description: 'Whether WYSIWYG editor is enabled.')]
    #[Groups(['mutation'])]
    public ?bool $enable_wysiwyg = null;

    /**
     * The validation.
     */
    #[ApiProperty(description: 'Validation rule.')]
    #[Groups(['mutation'])]
    public ?string $validation = null;

    /**
     * The regex.
     */
    #[ApiProperty(description: 'Custom regex pattern.')]
    #[Groups(['mutation'])]
    public ?string $regex = null;

    /**
     * The default value.
     */
    #[ApiProperty(description: 'Default value (0 or 1 for boolean type).')]
    #[Groups(['mutation'])]
    public ?string $default_value = null;

    /**
     * The position.
     */
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
     * Items with an "id" are updates; items without "id" are new; any existing.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty(description: 'Full replacement options array. Items with id are updated; without id are inserted; omitted ids are deleted.')]
    #[Groups(['mutation'])]
    public ?array $options = null;
}
