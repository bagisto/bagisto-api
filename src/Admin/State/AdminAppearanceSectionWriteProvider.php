<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSection;
use Webkul\BagistoApi\Exception\AuthenticationException;

class AdminAppearanceSectionWriteProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(protected AppearanceScopeGuard $scope) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminAppearanceSection
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminAppearanceSection;

        $placeholder->id = (int) ($this->scope->pathValue($uriVariables, 'id') ?? 0);

        return $placeholder;
    }
}
