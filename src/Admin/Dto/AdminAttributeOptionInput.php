<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAttributeOptionInput
{
    /**
     * The admin name.
     */
    #[ApiProperty(description: 'Option admin display name.')]
    #[Groups(['mutation'])]
    public ?string $admin_name = null;

    /**
     * The sort order.
     */
    #[ApiProperty(description: 'Option sort order.')]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    /**
     * The swatch value.
     */
    #[ApiProperty(description: 'Swatch value (hex color, image path, or text label depending on parent swatch_type).')]
    #[Groups(['mutation'])]
    public ?string $swatch_value = null;

    /**
     * Locale-keyed option translations, e.g. {"en": {"label": "Red"}, "fr": {"label": "Rouge"}}.
     *
     * @var array<string, array{label: string}>|null
     */
    #[ApiProperty(description: 'Locale-keyed label translations.')]
    #[Groups(['mutation'])]
    public ?array $translations = null;
}
