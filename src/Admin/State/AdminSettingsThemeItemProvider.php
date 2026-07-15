<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTheme;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Theme\Models\ThemeCustomization;

/**
 * Theme detail — GET /api/admin/settings/themes/{id} + adminSettingsTheme query.
 *
 * Branches: GraphQL → the AdminSettingsTheme Eloquent model (translations
 * resolves as a connection); REST → the flat AdminSettingsThemeRestDto.
 */
class AdminSettingsThemeItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsTheme|AdminSettingsThemeRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.theme.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = AdminSettingsTheme::with('translations')->find($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.theme.not-found'));
            }

            return $model;
        }

        $theme = ThemeCustomization::with('translations')->find($id);

        if (! $theme) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.theme.not-found'));
        }

        return $this->buildRestDto($theme);
    }

    /**
     * Public alias used by the processor to reuse the REST mapping logic.
     */
    public function buildRestDtoPublic(object $theme): AdminSettingsThemeRestDto
    {
        return $this->buildRestDto($theme);
    }

    protected function buildRestDto(object $theme): AdminSettingsThemeRestDto
    {
        /** @var ThemeCustomization $theme */
        $dto = new AdminSettingsThemeRestDto;

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
                'locale' => $tr->locale,
                'options' => $tr->options,
            ];
        }
        $dto->translations = $translations;

        return $dto;
    }
}
