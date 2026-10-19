<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingTemplateCreateInput
{
    /**
     * The name.
     */
    #[ApiProperty(description: 'Template display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Status enum: active | inactive | draft.')]
    #[Groups(['mutation'])]
    public ?string $status = null;

    /**
     * The content.
     */
    #[ApiProperty(description: 'HTML body content.')]
    #[Groups(['mutation'])]
    public ?string $content = null;
}
