<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminSettingsTheme (detail + listing).
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminSettingsThemeRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?int $sort_order = null;

    #[ApiProperty(writable: false)]
    public ?bool $status = null;

    #[ApiProperty(writable: false)]
    public ?int $channel_id = null;

    #[ApiProperty(writable: false)]
    public ?string $theme_code = null;

    /**
     * @var array<int, array{locale:string, options:array|null}>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
