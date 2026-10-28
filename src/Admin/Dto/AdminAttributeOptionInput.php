<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for create/update of a single attribute option.
 *
 * Used by:
 *   POST   /api/admin/catalog/attributes/{attributeId}/options
 *   PUT    /api/admin/catalog/attributes/{attributeId}/options/{optionId}
 *   + matching GraphQL mutations.
 */
class AdminAttributeOptionInput
{
    #[ApiProperty(description: 'Option admin display name.')]
    #[Groups(['mutation'])]
    public ?string $admin_name = null;

    #[ApiProperty(description: 'Option sort order.')]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    #[ApiProperty(description: 'Swatch value (hex color, image path, or text label depending on parent swatch_type).')]
    #[Groups(['mutation'])]
    public ?string $swatch_value = null;

    #[ApiProperty(description: 'Swatch image alt text, saved for the requested locale.')]
    #[Groups(['mutation'])]
    public ?string $swatch_alt = null;

    #[ApiProperty(description: 'Renames the stored swatch image (without extension). Image swatches only.')]
    #[Groups(['mutation'])]
    public ?string $swatch_file_name = null;

    #[ApiProperty(description: 'Locale the swatch alt text is saved for ("all" for every locale). Defaults to the default locale.')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /**
     * Locale-keyed option translations, e.g. {"en": {"label": "Red"}, "fr": {"label": "Rouge"}}.
     *
     * @var array<string, array{label: string}>|null
     */
    #[ApiProperty(description: 'Locale-keyed label translations.')]
    #[Groups(['mutation'])]
    public ?array $translations = null;
}
