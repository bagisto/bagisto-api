<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminCustomerApiSession;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Repositories\CustomerApiSessionRepository;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;

class AdminCustomerApiSessionProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;
    use ReadsAdminAuthInput;

    /**
     * Create a new customer session processor.
     */
    public function __construct(
        protected CustomerApiSessionRepository $sessionRepository,
        protected CustomerApiAuthService $authService,
    ) {}

    /**
     * End one live customer session; REST answers 204, GraphQL the ended session.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?AdminCustomerApiSession
    {
        $admin = $this->authorizedAdmin('customers.api_sessions.delete', 'bagistoapi::app.admin.customer-api-sessions.errors.no-permission');

        $session = $this->sessionRepository->findWithCustomer($this->idFromRequest($uriVariables, $context));

        if (
            ! $session
            || ! $session->isActive()
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.auth.session-not-found'));
        }

        $session = $this->authService->revokeSessionAsAdmin($admin, $session, request());

        if (! $operation instanceof GraphQlOperation) {
            return null;
        }

        $resource = AdminCustomerApiSession::fromSession($session);

        $resource->message = __('bagistoapi::app.graphql.auth.session-revoked');

        return $resource;
    }
}
