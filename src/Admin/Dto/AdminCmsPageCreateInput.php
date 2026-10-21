<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCmsPageCreateInput
{
    /**
     * The url key.
     */
    #[ApiProperty(description: 'URL key (slug). Must be unique across cms_page_translations and match slug regex.')]
    #[Groups(['mutation'])]
    public ?string $url_key = null;

    /**
     * The page title.
     */
    #[ApiProperty(description: 'Page title.')]
    #[Groups(['mutation'])]
    public ?string $page_title = null;

    /**
     * The html content.
     */
    #[ApiProperty(description: 'Page HTML content.')]
    #[Groups(['mutation'])]
    public ?string $html_content = null;

    /**
     * The ids of the channels to attach the CMS page to.
     *
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'Channel IDs to attach the page to.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    /**
     * The meta title.
     */
    #[ApiProperty(description: 'SEO meta title.')]
    #[Groups(['mutation'])]
    public ?string $meta_title = null;

    /**
     * The meta keywords.
     */
    #[ApiProperty(description: 'SEO meta keywords.')]
    #[Groups(['mutation'])]
    public ?string $meta_keywords = null;

    /**
     * The meta description.
     */
    #[ApiProperty(description: 'SEO meta description.')]
    #[Groups(['mutation'])]
    public ?string $meta_description = null;
}
