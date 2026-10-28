<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminSessionResource;
use Webkul\BagistoApi\Admin\Repositories\AdminApiSessionRepository;

abstract class AbstractAdminSessionCollectionProvider implements ProviderInterface
{
    use MapsAdminApiSession;
    use ResolvesAdminPaging;

    /**
     * The page size when none is asked for.
     */
    protected const DEFAULT_PER_PAGE = 10;

    /**
     * The largest page size.
     */
    protected const MAX_PER_PAGE = 50;

    /**
     * Create a new session collection provider.
     */
    public function __construct(protected AdminApiSessionRepository $sessionRepository) {}

    /**
     * Authorize the request and return the id of the admin whose sessions are listed, or null for every admin.
     */
    abstract protected function authorizeAndScope(array $uriVariables, array $args): ?int;

    /**
     * A new, empty resource of the listed type.
     */
    abstract protected function newResource(): AdminSessionResource;

    /**
     * A page of live sessions, newest first, filtered by client type.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $args = $context['args'] ?? request()->query();

        $adminId = $this->authorizeAndScope($uriVariables, $args);

        [$page, $perPage] = $this->resolvePaging($args);

        $paginator = $this->sessionRepository->paginateActive($adminId, $this->clientType($args), $page, $perPage);

        $currentSessionId = AdminAuthHelper::credential()?->session?->id;

        $paginator->setCollection($paginator->getCollection()->map(
            fn (AdminApiSession $session) => $this->mapSession($session, $this->newResource(), $currentSessionId)
        ));

        return new Paginator($paginator);
    }

    /**
     * The client type filter, ignored when it is not a known type.
     */
    protected function clientType(array $args): ?string
    {
        $clientType = $args['client_type'] ?? $args['clientType'] ?? null;

        return in_array($clientType, AdminApiSession::CLIENT_TYPES, true)
            ? $clientType
            : null;
    }
}
