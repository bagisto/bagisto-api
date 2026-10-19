<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminConfigurationUpdateInput
{
    /**
     * The slug.
     */
    #[ApiProperty(description: 'Slug of the configuration node being updated, e.g. `sales.order_settings`.')]
    #[Groups(['mutation'])]
    public ?string $slug = null;

    /**
     * The channel.
     */
    #[ApiProperty(description: 'Channel code for channel-based fields.')]
    #[Groups(['mutation'])]
    public ?string $channel = null;

    /**
     * The locale.
     */
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
