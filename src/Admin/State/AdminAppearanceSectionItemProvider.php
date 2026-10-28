<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAppearanceSection;

class AdminAppearanceSectionItemProvider implements ProviderInterface
{
    use ChecksAdminPermission;
    use MapsAppearanceSection;

    public function __construct(protected AppearanceScopeGuard $scope) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->authorizedAdmin('appearance.sections', 'bagistoapi::app.admin.appearance.no-permission');

        $id = $this->scope->pathValue($uriVariables, 'id') ?? $context['args']['id'] ?? null;

        $section = $this->scope->sectionOrFail($id ? (int) basename((string) $id) : null);

        return $this->isGraphQL($context)
            ? $this->toEloquent($section)
            : $this->toRestDto($section);
    }
}
