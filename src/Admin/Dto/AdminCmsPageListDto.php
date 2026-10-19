<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(
    shortName: 'AdminCmsPageListDto',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminCmsPageListDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    public ?int $id = null;

    /**
     * The url key.
     */
    public ?string $url_key = null;

    /**
     * The page title.
     */
    public ?string $page_title = null;

    /**
     * The html content.
     */
    public ?string $html_content = null;

    /**
     * The meta title.
     */
    public ?string $meta_title = null;

    /**
     * The meta keywords.
     */
    public ?string $meta_keywords = null;

    /**
     * The meta description.
     */
    public ?string $meta_description = null;

    /**
     * The layout.
     */
    public ?string $layout = null;

    /**
     * The preview url.
     */
    public ?string $preview_url = null;

    /**
     * The locale.
     */
    public ?string $locale = null;

    /**
     * The channel.
     */
    public ?string $channel = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;

    /** @var array<int, mixed>|null */
    public ?array $translations = null;

    /** @var array<int, mixed>|null */
    public ?array $channels = null;
}
