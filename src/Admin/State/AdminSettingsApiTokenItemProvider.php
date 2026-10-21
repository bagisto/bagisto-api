<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminSettingsApiToken;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAdminApiToken;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

class AdminSettingsApiTokenItemProvider implements ProviderInterface
{
    use ChecksAdminPermission;
    use MapsAdminApiToken;
    use ReadsAdminAuthInput;

    /**
     * Create a new API token item provider.
     */
    public function __construct(
        protected AdminApiTokenRepository $apiTokenRepository,
        protected AdminApiTokenIssuer $issuer,
    ) {}

    /**
     * One API token the caller may see; another admin's token needs manage_others and is a 404 otherwise.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsApiToken
    {
        $actor = $this->authorizedAdmin('settings.api_tokens', 'bagistoapi::app.admin.api-tokens.errors.no-permission');

        $id = $this->idFromRequest($uriVariables, $context);

        $token = $id > 0
            ? $this->apiTokenRepository->findWithAdmin($id)
            : null;

        if (
            ! $token
            || ! $this->issuer->canManage($actor, $token)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.api-tokens.errors.not-found'));
        }

        return $this->mapApiToken($token);
    }
}
