<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminSession;
use Webkul\BagistoApi\Admin\Repositories\AdminApiSessionRepository;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthService;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAdminApiSession;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Admin\State\Concerns\RequiresAdminSession;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

class AdminSessionProcessor implements ProcessorInterface
{
    use MapsAdminApiSession;
    use ReadsAdminAuthInput;
    use RequiresAdminSession;

    /**
     * Create a new session processor.
     */
    public function __construct(
        protected AdminApiSessionRepository $sessionRepository,
        protected AdminApiAuthService $authService,
    ) {}

    /**
     * Revoke one live session of the signed-in admin; REST answers 204, GraphQL the revoked session.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?AdminSession
    {
        $credential = $this->sessionCredential();

        $session = $this->sessionRepository->findActiveForAdmin(
            (int) $credential->admin->id,
            $this->idFromRequest($uriVariables, $context),
        );

        if (! $session) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.auth.sessions.not-found'));
        }

        $session = $this->authService->revokeSession($credential->admin, $session, request());

        if (! $operation instanceof GraphQlOperation) {
            return null;
        }

        $resource = $this->mapSession($session, new AdminSession, (int) $credential->session->id);
        $resource->message = __('bagistoapi::app.admin.auth.sessions.revoked');

        return $resource;
    }
}
