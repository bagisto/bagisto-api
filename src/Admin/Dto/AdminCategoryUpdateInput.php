<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/catalog/categories/{id}.
 *
 * Update validation is LOCALE-NESTED in monolith CategoryRequest:
 *   <locale>.slug / <locale>.name / <locale>.description are required.
 * Plus top-level: position, attributes, parent_id, display_mode, etc.
 *
 * The REST/GraphQL clients send the same nested shape — the processor passes
 * the payload through to CategoryRepository::update() which understands it.
 */
class AdminCategoryUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/catalog/categories/12).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Locale code (e.g. "en"). Determines which nested locale block is required.')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    #[ApiProperty(description: 'Sort position among siblings.')]
    #[Groups(['mutation'])]
    public ?int $position = null;

    /** @var array<int, int>|null */
    #[ApiProperty(description: 'Filterable attribute IDs to sync onto the category.')]
    #[Groups(['mutation'])]
    public ?array $attributes = null;

    #[ApiProperty(description: 'Parent category ID. Change this (with position) to move the category.')]
    #[Groups(['mutation'])]
    public ?int $parent_id = null;

    #[ApiProperty(description: 'Display mode.')]
    #[Groups(['mutation'])]
    public ?string $display_mode = null;

    #[ApiProperty(description: 'Status (1 = enabled, 0 = disabled).')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /** @var array<int, string>|null */
    #[ApiProperty(description: 'Optional logo path(s). File-upload is not supported in v1.')]
    #[Groups(['mutation'])]
    public ?array $logo_path = null;

    /** @var array<int, string>|null */
    #[ApiProperty(description: 'Optional banner path(s). File-upload is not supported in v1.')]
    #[Groups(['mutation'])]
    public ?array $banner_path = null;

    /**
     * Per-locale translation block. Keyed by locale code (e.g. "en"), with
     * keys: slug, name, description, meta_title, meta_description, meta_keywords.
     *
     * @var array<string, mixed>|null
     */
    #[ApiProperty(description: 'Per-locale translation block: { en: { slug, name, description, ... } }.')]
    #[Groups(['mutation'])]
    public ?array $en = null;
}
