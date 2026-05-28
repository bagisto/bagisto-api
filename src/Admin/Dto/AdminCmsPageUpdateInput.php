<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/cms/pages/{id} + updateAdminCmsPage.
 *
 * Update validation is LOCALE-NESTED in monolith PageController::update():
 *   <locale>.url_key / <locale>.page_title / <locale>.html_content are required.
 * Plus top-level: channels (required), locale (required).
 *
 * The processor reads the body from request()->all() (REST) or
 * $context['args']['input'] (GraphQL) because the name converter does not
 * map dynamic locale-keyed blocks onto DTO properties cleanly. The 'en'
 * property below is a hint for the GraphQL schema — for other locales the
 * processor falls back to the raw args.
 */
class AdminCmsPageUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/cms/pages/12).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

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
