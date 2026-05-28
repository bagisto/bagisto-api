<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/search-synonyms.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SearchSynonymController::store:
 *   - name  required
 *   - terms required (comma-separated string, e.g. "shirt,tshirt,tee")
 */
class AdminMarketingSearchSynonymCreateInput
{
    #[ApiProperty(description: 'Synonym group display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Comma-separated list of synonym terms (e.g. "shirt,tshirt,tee").')]
    #[Groups(['mutation'])]
    public ?string $terms = null;
}
