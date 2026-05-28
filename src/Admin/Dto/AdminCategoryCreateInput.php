<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/catalog/categories.
 *
 * Mirrors Bagisto core CategoryRequest create-mode rules:
 *   - top-level: slug (required + unique via ProductCategoryUniqueSlug)
 *   - name (required)
 *   - position (required int)
 *   - attributes (required array of filterable attribute IDs)
 *   - description (required when display_mode in description_only/products_and_description)
 *   - optional: parent_id, display_mode, meta_title, meta_description, meta_keywords, status,
 *               logo_path, banner_path, locale
 */
class AdminCategoryCreateInput
{
    #[ApiProperty(description: 'Category slug (unique across category translations + product slugs).')]
    #[Groups(['mutation'])]
    public ?string $slug = null;

    #[ApiProperty(description: 'Category display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Category description. Required when display_mode is description_only or products_and_description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty(description: 'Sort position among siblings.')]
    #[Groups(['mutation'])]
    public ?int $position = null;

    /** @var array<int, int>|null */
    #[ApiProperty(description: 'List of filterable attribute IDs to attach.')]
    #[Groups(['mutation'])]
    public ?array $attributes = null;

    #[ApiProperty(description: 'Parent category ID. Null/omitted for a root category.')]
    #[Groups(['mutation'])]
    public ?int $parent_id = null;

    #[ApiProperty(description: 'Display mode (products_and_description, products_only, description_only).')]
    #[Groups(['mutation'])]
    public ?string $display_mode = null;

    #[ApiProperty(description: 'Locale code for the translation. Defaults to the requested locale.')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    #[ApiProperty(description: 'Status (1 = enabled, 0 = disabled).')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty(description: 'SEO meta title.')]
    #[Groups(['mutation'])]
    public ?string $meta_title = null;

    #[ApiProperty(description: 'SEO meta description.')]
    #[Groups(['mutation'])]
    public ?string $meta_description = null;

    #[ApiProperty(description: 'SEO meta keywords.')]
    #[Groups(['mutation'])]
    public ?string $meta_keywords = null;

    /** @var array<int, string>|null */
    #[ApiProperty(description: 'Optional logo path(s). File-upload is not supported in v1 — accepts pre-uploaded storage paths.')]
    #[Groups(['mutation'])]
    public ?array $logo_path = null;

    /** @var array<int, string>|null */
    #[ApiProperty(description: 'Optional banner path(s). File-upload is not supported in v1 — accepts pre-uploaded storage paths.')]
    #[Groups(['mutation'])]
    public ?array $banner_path = null;
}
