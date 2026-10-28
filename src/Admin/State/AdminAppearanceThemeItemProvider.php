<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAppearanceTheme;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Theme\ThemeCatalog;

class AdminAppearanceThemeItemProvider implements ProviderInterface
{
    use ChecksAdminPermission;
    use MapsAppearanceTheme;

    public function __construct(
        protected ThemeCatalog $themeCatalog,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->authorizedAdmin('appearance.themes', 'bagistoapi::app.admin.appearance.no-permission');

        $code = $this->scope->pathValue($uriVariables, 'code') ?? $context['args']['code'] ?? null;

        $row = $code ? $this->themeCatalog->find((string) $code) : null;

        if (! $row) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.appearance.theme.not-found'));
        }

        return $this->toThemeResource($row);
    }
}
