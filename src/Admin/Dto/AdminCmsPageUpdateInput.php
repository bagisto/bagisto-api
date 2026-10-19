<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCmsPageUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/cms/pages/12).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The locale.
     */
    #[ApiProperty(description: 'Locale code (e.g. "en"). Determines which nested locale block is required.')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /** @var array<int, int>|null */
    #[ApiProperty(description: 'Channel IDs to sync onto the page.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    /**
     * English-locale block. Other locales (fr/es/...) are accepted at runtime by
     * reading raw args/body — declared here only so the GraphQL schema is buildable.
     *
     * @var array<string, mixed>|null
     */
    #[ApiProperty(description: 'Per-locale translation block: { en: { url_key, page_title, html_content, ... } }.')]
    #[Groups(['mutation'])]
    public ?array $en = null;
}
