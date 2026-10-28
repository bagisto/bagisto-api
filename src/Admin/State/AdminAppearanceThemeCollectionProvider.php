<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAppearanceTheme;
use Webkul\Theme\ThemeCatalog;

class AdminAppearanceThemeCollectionProvider implements ProviderInterface
{
    use ChecksAdminPermission;
    use MapsAppearanceTheme;

    public function __construct(protected ThemeCatalog $themeCatalog) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->authorizedAdmin('appearance.themes', 'bagistoapi::app.admin.appearance.no-permission');

        return $this->themeCatalog
            ->all()
            ->map(fn (array $row) => $this->toThemeResource($row))
            ->values()
            ->all();
    }
}
