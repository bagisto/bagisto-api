<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsLocale;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Core\Models\Locale;

class AdminSettingsLocaleItemProvider extends AbstractAdminItemProvider
{
    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.locale.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return Locale::find($id);
    }

    /**
     * Map to dto.
     */
    protected function mapToDto(object $locale): AdminSettingsLocale
    {
        $dto = new AdminSettingsLocale;

        $dto->id = (int) $locale->id;
        $dto->code = $locale->code;
        $dto->name = $locale->name;
        $dto->direction = $locale->direction;
        $dto->logoPath = $locale->logo_path ?? null;
        $dto->logoUrl = $locale->logo_url ?? null;
        $dto->createdAt = $locale->created_at?->toIso8601String();
        $dto->updatedAt = $locale->updated_at?->toIso8601String();

        return $dto;
    }
}
