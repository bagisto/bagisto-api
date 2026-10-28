<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerSession;
use Webkul\BagistoApi\Repositories\CustomerApiSessionRepository;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;
use Webkul\BagistoApi\Support\CurrentCustomer;

class CustomerSessionProcessor implements ProcessorInterface
{
    use ReadsAdminAuthInput;

    /**
     * Create a new customer session processor.
     */
    public function __construct(
        protected CustomerApiSessionRepository $sessionRepository,
        protected CustomerApiAuthService $authService,
    ) {}

    /**
     * End one live session of the signed-in customer; REST answers 204, GraphQL the ended session.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?CustomerSession
    {
        $current = CurrentCustomer::requireSession();

        $session = $this->sessionRepository->findActiveForCustomer(
            (int) $current->customer_id,
            $this->idFromRequest($uriVariables, $context),
        );

        if (! $session) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.auth.session-not-found'));
        }

        $session = $this->authService->revokeSession($session);

        if (! $operation instanceof GraphQlOperation) {
            return null;
        }

        $resource = CustomerSession::fromSession($session, (int) $current->id);

        $resource->message = __('bagistoapi::app.graphql.auth.session-revoked');

        return $resource;
    }
}
