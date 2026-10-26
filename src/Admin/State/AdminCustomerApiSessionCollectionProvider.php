<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminCustomerApiSession;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\ResolvesAdminPaging;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Repositories\CustomerApiSessionRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class AdminCustomerApiSessionCollectionProvider implements ProviderInterface
{
    use ChecksAdminPermission;
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
     * Create a new customer session provider.
     */
    public function __construct(
        protected CustomerApiSessionRepository $sessionRepository,
        protected CustomerRepository $customerRepository,
    ) {}

    /**
     * A page of live customer sessions, newest first, of every customer or of one.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->authorizedAdmin('customers.api_sessions', 'bagistoapi::app.admin.customer-api-sessions.errors.no-permission');

        $args = $context['args'] ?? request()->query();

        $customerId = $this->customerId($uriVariables, $args);

        [$page, $perPage] = $this->resolvePaging($args);

        $paginator = $this->sessionRepository->paginateActive(
            $customerId,
            $this->clientType($args),
            $this->positiveInt($args['channel_id'] ?? $args['channelId'] ?? null),
            $page,
            $perPage,
        );

        $paginator->setCollection($paginator->getCollection()->map(
            fn (CustomerApiSession $session) => AdminCustomerApiSession::fromSession($session)
        ));

        return new Paginator($paginator);
    }

    /**
     * The customer from the path, which must exist, or the optional filter.
     */
    protected function customerId(array $uriVariables, array $args): ?int
    {
        $fromPath = $uriVariables['customerId'] ?? request()->route('customerId');

        if ($fromPath === null) {
            return $this->positiveInt($args['customer_id'] ?? $args['customerId'] ?? null);
        }

        $customerId = (int) $fromPath;

        if (! $this->customerRepository->find($customerId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        return $customerId;
    }

    /**
     * The client type filter, ignored when it is not a known type.
     */
    protected function clientType(array $args): ?string
    {
        $clientType = $args['client_type'] ?? $args['clientType'] ?? null;

        return in_array($clientType, CustomerApiSession::CLIENT_TYPES, true)
            ? $clientType
            : null;
    }

    /**
     * A positive integer filter value, or null.
     */
    protected function positiveInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0
            ? (int) $value
            : null;
    }
}
