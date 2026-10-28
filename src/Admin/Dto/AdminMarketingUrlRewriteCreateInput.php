<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingUrlRewriteCreateInput
{
    /**
     * The entity type.
     */
    #[ApiProperty(description: 'Entity type. One of: product, category, cms_page.')]
    #[Groups(['mutation'])]
    public ?string $entity_type = null;

    /**
     * The request path.
     */
    #[ApiProperty(description: 'Public URL to be rewritten.')]
    #[Groups(['mutation'])]
    public ?string $request_path = null;

    /**
     * The target path.
     */
    #[ApiProperty(description: 'Target URL the request_path points to.')]
    #[Groups(['mutation'])]
    public ?string $target_path = null;

    /**
     * The redirect type.
     */
    #[ApiProperty(description: 'HTTP redirect status. One of: 301, 302.')]
    #[Groups(['mutation'])]
    public ?string $redirect_type = null;

    /**
     * The locale.
     */
    #[ApiProperty(description: 'Locale code (must exist in locales table).')]
    #[Groups(['mutation'])]
    public ?string $locale = null;
}
