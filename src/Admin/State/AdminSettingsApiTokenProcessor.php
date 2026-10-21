<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminSettingsApiToken;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAdminApiToken;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

class AdminSettingsApiTokenProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;
    use MapsAdminApiToken;
    use ReadsAdminAuthInput;

    /**
     * Create a new API token processor.
     */
    public function __construct(
        protected AdminApiTokenRepository $apiTokenRepository,
        protected AdminApiTokenIssuer $issuer,
    ) {}

    /**
     * Revoke an API token the caller may manage; REST answers 204, GraphQL the revoked token.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?AdminSettingsApiToken
    {
        $actor = $this->authorizedAdmin('settings.api_tokens.delete', 'bagistoapi::app.admin.api-tokens.errors.no-permission');

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

        $token = $this->issuer->revoke($actor, $token, request());

        if (! $operation instanceof GraphQlOperation) {
            return null;
        }

        $resource = $this->mapApiToken($token);
        $resource->message = __('bagistoapi::app.admin.api-tokens.messages.revoked');

        return $resource;
    }
}
