<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminAppearanceSectionRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The type.
     */
    public ?string $type = null;

    /**
     * The theme code.
     */
    public ?string $theme_code = null;

    /**
     * The channel id.
     */
    public ?int $channel_id = null;

    /**
     * The sort order.
     */
    public ?int $sort_order = null;

    /**
     * The status.
     */
    public ?int $status = null;

    /**
     * The draft status.
     */
    public ?bool $draft_status = null;

    /**
     * The draft sort order.
     */
    public ?int $draft_sort_order = null;

    /**
     * The has draft.
     */
    public ?bool $has_draft = null;

    /**
     * The is pinned.
     */
    public ?bool $is_pinned = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $translations = [];

    /**
     * The message.
     */
    public ?string $message = null;
}
