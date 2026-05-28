<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/locales.
 *
 * Mirrors Bagisto admin LocaleController::store validation:
 *   - code: required, unique in locales, Code rule (lowercase letters/digits)
 *   - name: required
 *   - direction: required, in:ltr,rtl
 *   - logo_path: deferred — path string only in v1 (no upload)
 */
class AdminSettingsLocaleCreateInput
{
    #[ApiProperty(description: 'Locale code (e.g. en, fr). Lowercase letters/digits, unique.')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Locale display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Text direction: ltr or rtl.')]
    #[Groups(['mutation'])]
    public ?string $direction = null;

    #[ApiProperty(description: 'Logo path string (image upload deferred in v1).')]
    #[Groups(['mutation'])]
    public ?string $logo_path = null;
}
