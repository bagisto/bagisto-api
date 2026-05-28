<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/events.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\EventController::store
 * validation rules: name required, description required, date required|date.
 */
class AdminMarketingEventCreateInput
{
    #[ApiProperty(description: 'Event display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Long-form description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty(description: 'Event trigger date (Y-m-d).')]
    #[Groups(['mutation'])]
    public ?string $date = null;
}
