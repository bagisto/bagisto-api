<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCategoryCreateInput
{
    /**
     * The slug.
     */
    #[ApiProperty(description: 'Category slug (unique across category translations + product slugs).')]
    #[Groups(['mutation'])]
    public ?string $slug = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Category display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(description: 'Category description. Required when display_mode is description_only or products_and_description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The position.
     */
    #[ApiProperty(description: 'Sort position among siblings.')]
    #[Groups(['mutation'])]
    public ?int $position = null;

    /**
     * The ids of the filterable attributes to attach to the category.
     *
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'List of filterable attribute IDs to attach.')]
    #[Groups(['mutation'])]
    public ?array $attributes = null;

    /**
     * The parent id.
     */
    #[ApiProperty(description: 'Parent category ID. Null/omitted for a root category.')]
    #[Groups(['mutation'])]
    public ?int $parent_id = null;

    /**
     * The display mode.
     */
    #[ApiProperty(description: 'Display mode (products_and_description, products_only, description_only).')]
    #[Groups(['mutation'])]
    public ?string $display_mode = null;

    /**
     * The locale.
     */
    #[ApiProperty(description: 'Locale code for the translation. Defaults to the requested locale.')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Status (1 = enabled, 0 = disabled).')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The meta title.
     */
    #[ApiProperty(description: 'SEO meta title.')]
    #[Groups(['mutation'])]
    public ?string $meta_title = null;

    /**
     * The meta description.
     */
    #[ApiProperty(description: 'SEO meta description.')]
    #[Groups(['mutation'])]
    public ?string $meta_description = null;

    /**
     * The meta keywords.
     */
    #[ApiProperty(description: 'SEO meta keywords.')]
    #[Groups(['mutation'])]
    public ?string $meta_keywords = null;

    /**
     * The pre-uploaded storage paths of the category logo.
     *
     * @var array<int, string>|null
     */
    #[ApiProperty(description: 'Optional logo path(s). File-upload is not supported in v1 — accepts pre-uploaded storage paths.')]
    #[Groups(['mutation'])]
    public ?array $logo_path = null;

    /**
     * The pre-uploaded storage paths of the category banner.
     *
     * @var array<int, string>|null
     */
    #[ApiProperty(description: 'Optional banner path(s). File-upload is not supported in v1 — accepts pre-uploaded storage paths.')]
    #[Groups(['mutation'])]
    public ?array $banner_path = null;
}
