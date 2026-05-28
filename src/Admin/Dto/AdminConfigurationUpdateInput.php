<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for `POST /api/admin/configuration` and
 * `createAdminConfigurationUpdate` GraphQL mutation.
 *
 * Shape:
 *   {
 *     "slug": "sales.order_settings",
 *     "channel": "default",                // optional
 *     "locale":  "en",                     // optional
 *     "values": { "<full.dotted.code>": <string|bool|number> }
 *   }
 *
 * For REST multipart uploads, `values[<code>]` is the file part; the processor
 * forwards `request()` to `CoreConfigRepository::create()` which handles
 * `hasFile()` natively. GraphQL rejects file-type fields (mirrors Phase 5.11).
 */
class AdminConfigurationUpdateInput
{
    #[ApiProperty(description: 'Slug of the configuration node being updated, e.g. `sales.order_settings`.')]
    #[Groups(['mutation'])]
    public ?string $slug = null;

    #[ApiProperty(description: 'Channel code for channel-based fields.')]
    #[Groups(['mutation'])]
    public ?string $channel = null;

    #[ApiProperty(description: 'Locale code for locale-based fields.')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /**
     * Flat map: `<full.dotted.code> => <value>`. Every key must be inside the
     * `slug` subtree (anti-scope-escape).
     *
     * @var array<string, mixed>|null
     */
    #[ApiProperty(description: 'Flat map of dotted field codes to values.')]
    #[Groups(['mutation'])]
    public ?array $values = null;
}
