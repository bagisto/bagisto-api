<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsTheme;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Theme\Models\ThemeCustomization;

class AdminSettingsThemeItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.theme.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return ThemeCustomization::with('translations')->find($id);
    }

    protected function mapToDto(object $theme): AdminSettingsTheme
    {
        /** @var ThemeCustomization $theme */
        $dto = new AdminSettingsTheme;

        $dto->id = (int) $theme->id;
        $dto->name = $theme->name;
        $dto->type = $theme->type;
        $dto->sortOrder = (int) $theme->sort_order;
        $dto->status = (bool) $theme->status;
        $dto->channelId = (int) $theme->channel_id;
        $dto->themeCode = $theme->theme_code;
        $dto->createdAt = $theme->created_at?->toIso8601String();
        $dto->updatedAt = $theme->updated_at?->toIso8601String();

        $translations = [];
        foreach ($theme->translations ?? [] as $tr) {
            $translations[] = [
                'locale'  => $tr->locale,
                'options' => $tr->options,
            ];
        }
        $dto->translations = $translations;

        return $dto;
    }
}
