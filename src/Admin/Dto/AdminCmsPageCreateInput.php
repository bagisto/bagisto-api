<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/cms/pages + createAdminCmsPage.
 *
 * Mirrors Bagisto core PageController::store():
 *   - top-level: url_key (required + unique on cms_page_translations.url_key + slug regex)
 *   - page_title (required)
 *   - html_content (required)
 *   - channels (required non-empty array of channel ids)
 *   - optional: meta_title, meta_keywords, meta_description
 *
 * The repository broadcasts top-level translated fields to every locale.
 */
class AdminCmsPageCreateInput
{
    #[ApiProperty(description: 'URL key (slug). Must be unique across cms_page_translations and match slug regex.')]
    #[Groups(['mutation'])]
    public ?string $url_key = null;

    #[ApiProperty(description: 'Page title.')]
    #[Groups(['mutation'])]
    public ?string $page_title = null;

    #[ApiProperty(description: 'Page HTML content.')]
    #[Groups(['mutation'])]
    public ?string $html_content = null;

    /** @var array<int, int>|null */
    #[ApiProperty(description: 'Channel IDs to attach the page to.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    #[ApiProperty(description: 'SEO meta title.')]
    #[Groups(['mutation'])]
    public ?string $meta_title = null;

    #[ApiProperty(description: 'SEO meta keywords.')]
    #[Groups(['mutation'])]
    public ?string $meta_keywords = null;

    #[ApiProperty(description: 'SEO meta description.')]
    #[Groups(['mutation'])]
    public ?string $meta_description = null;
}
