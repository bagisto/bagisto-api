<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/templates.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\TemplateController::store:
 *   - name    required
 *   - status  required|in:active,inactive,draft
 *   - content required
 */
class AdminMarketingTemplateCreateInput
{
    #[ApiProperty(description: 'Template display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Status enum: active | inactive | draft.')]
    #[Groups(['mutation'])]
    public ?string $status = null;

    #[ApiProperty(description: 'HTML body content.')]
    #[Groups(['mutation'])]
    public ?string $content = null;
}
