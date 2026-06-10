<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output DTO for the admin CMS Pages listing.
 *
 * Reproduces the exact list-row shape served before the GraphQL field-selection
 * refactor. The properties are snake_case (resolve over both transports) and
 * surface as camelCase to clients via the project name converter.
 */
#[ApiResource(
    shortName: 'AdminCmsPageListDto',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminCmsPageListDto
{
    use AcceptsCamelCaseWrites;

    public ?int $id = null;

    public ?string $url_key = null;

    public ?string $page_title = null;

    public ?string $html_content = null;

    public ?string $meta_title = null;

    public ?string $meta_keywords = null;

    public ?string $meta_description = null;

    public ?string $layout = null;

    public ?string $preview_url = null;

    public ?string $locale = null;

    public ?string $channel = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    /** @var array<int, mixed>|null */
    public ?array $translations = null;

    /** @var array<int, mixed>|null */
    public ?array $channels = null;
}
