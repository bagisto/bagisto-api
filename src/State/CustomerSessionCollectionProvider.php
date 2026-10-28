<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\CustomerSession;
use Webkul\BagistoApi\Repositories\CustomerApiSessionRepository;
use Webkul\BagistoApi\Support\CurrentCustomer;

class CustomerSessionCollectionProvider implements ProviderInterface
{
    /**
     * Create a new customer session provider.
     */
    public function __construct(protected CustomerApiSessionRepository $sessionRepository) {}

    /**
     * The signed-in customer's live sessions, newest first.
     *
     * @return array<int, CustomerSession>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $current = CurrentCustomer::requireSession();

        return $this->sessionRepository
            ->activeForCustomer((int) $current->customer_id)
            ->map(fn ($session) => CustomerSession::fromSession($session, (int) $current->id))
            ->values()
            ->all();
    }
}
