<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/catalog/attributes.
 */
class AdminAttributeCreateInput
{
    #[ApiProperty(description: 'Attribute code (lowercase letters, digits, underscore only).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Admin display name.')]
    #[Groups(['mutation'])]
    public ?string $admin_name = null;

    #[ApiProperty(description: 'Attribute type (text, textarea, price, boolean, select, multiselect, checkbox, date, datetime, image, file).')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    #[ApiProperty(description: 'Swatch display type (text, color, image).')]
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

    #[ApiProperty(description: 'Whether WYSIWYG editor is enabled (for textarea type).')]
    #[Groups(['mutation'])]
    public ?bool $enable_wysiwyg = null;

    #[ApiProperty(description: 'Validation rule (required, url, email, regex, etc).')]
    #[Groups(['mutation'])]
    public ?string $validation = null;

    #[ApiProperty(description: 'Custom regex pattern (used when validation=regex).')]
    #[Groups(['mutation'])]
    public ?string $regex = null;

    #[ApiProperty(description: 'Default value (0 or 1 for boolean type).')]
    #[Groups(['mutation'])]
    public ?string $default_value = null;

    #[ApiProperty(description: 'Sort position.')]
    #[Groups(['mutation'])]
    public ?int $position = null;

    /**
     * Locale-keyed translations, e.g. {"en": {"name": "Color"}, "fr": {"name": "Couleur"}}.
     *
     * @var array<string, array{name: string}>|null
     */
    #[ApiProperty(description: 'Locale-keyed translations map.')]
    #[Groups(['mutation'])]
    public ?array $translations = null;

    /**
     * Array of option objects (for select/multiselect/checkbox types).
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty(description: 'Options array (for select/multiselect/checkbox types).')]
    #[Groups(['mutation'])]
    public ?array $options = null;
}
