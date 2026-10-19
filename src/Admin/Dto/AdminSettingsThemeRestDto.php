<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminSettingsThemeRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The type.
     */
    #[ApiProperty(writable: false)]
    public ?string $type = null;

    /**
     * The sort order.
     */
    #[ApiProperty(writable: false)]
    public ?int $sort_order = null;

    /**
     * The status.
     */
    #[ApiProperty(writable: false)]
    public ?bool $status = null;

    /**
     * The channel id.
     */
    #[ApiProperty(writable: false)]
    public ?int $channel_id = null;

    /**
     * The theme code.
     */
    #[ApiProperty(writable: false)]
    public ?string $theme_code = null;

    /**
     * @var array<int, array{locale:string, options:array|null}>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    /**
     * The created at.
     */
    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
