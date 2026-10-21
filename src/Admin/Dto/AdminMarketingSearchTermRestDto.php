<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminMarketingSearchTermRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The term.
     */
    #[ApiProperty(writable: false)]
    public ?string $term = null;

    /**
     * The results.
     */
    #[ApiProperty(writable: false)]
    public ?int $results = null;

    /**
     * The uses.
     */
    #[ApiProperty(writable: false)]
    public ?int $uses = null;

    /**
     * The redirect url.
     */
    #[ApiProperty(writable: false)]
    public ?string $redirect_url = null;

    /**
     * The locale.
     */
    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    /**
     * The channel the search term was recorded on.
     *
     * @var array{id:int, code:string|null, name:string|null}|null
     */
    #[ApiProperty(writable: false)]
    public ?array $channel = null;

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
