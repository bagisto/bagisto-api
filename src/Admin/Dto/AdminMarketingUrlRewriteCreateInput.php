<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/url-rewrites.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\URLRewriteController::store:
 *   - entity_type   required|in:product,category,cms_page
 *   - request_path  required
 *   - target_path   required
 *   - redirect_type required|in:301,302
 *   - locale        required|exists:locales,code
 */
class AdminMarketingUrlRewriteCreateInput
{
    #[ApiProperty(description: 'Entity type. One of: product, category, cms_page.')]
    #[Groups(['mutation'])]
    public ?string $entity_type = null;

    #[ApiProperty(description: 'Public URL to be rewritten.')]
    #[Groups(['mutation'])]
    public ?string $request_path = null;

    #[ApiProperty(description: 'Target URL the request_path points to.')]
    #[Groups(['mutation'])]
    public ?string $target_path = null;

    #[ApiProperty(description: 'HTTP redirect status. One of: 301, 302.')]
    #[Groups(['mutation'])]
    public ?string $redirect_type = null;

    #[ApiProperty(description: 'Locale code (must exist in locales table).')]
    #[Groups(['mutation'])]
    public ?string $locale = null;
}
