<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST shape of a section: the flat row plus its translations inline.
 *
 * Every property name matches an attribute or relation on AdminAppearanceSection — a DTO
 * field that does not is silently dropped by the serializer.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminAppearanceSectionRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $type = null;

    public ?string $theme_code = null;

    public ?int $channel_id = null;

    public ?int $sort_order = null;

    public ?int $status = null;

    public ?bool $draft_status = null;

    public ?int $draft_sort_order = null;

    public ?bool $has_draft = null;

    public ?bool $is_pinned = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $translations = [];

    public ?string $message = null;
}
