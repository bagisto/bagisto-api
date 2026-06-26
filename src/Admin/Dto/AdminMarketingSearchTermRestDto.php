<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminMarketingSearchTerm. Snake_case props surface as
 * camelCase via the central converter.
 *
 * The former bare `channelId` / `channelName` scalars are REPLACED by a single
 * `channel` object `{ id, code, name }` (to-one). Name-match trap: the prop must
 * be named `channel` to match the parent Eloquent resource's `channel()` relation.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminMarketingSearchTermRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $term = null;

    #[ApiProperty(writable: false)]
    public ?int $results = null;

    #[ApiProperty(writable: false)]
    public ?int $uses = null;

    #[ApiProperty(writable: false)]
    public ?string $redirect_url = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    /** @var array{id:int, code:string|null, name:string|null}|null */
    #[ApiProperty(writable: false)]
    public ?array $channel = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
