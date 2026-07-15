<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerReturnMessage;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;

class CustomerReturnMessageProvider implements ProviderInterface
{
    public function __construct(
        private readonly RMARepository $rmaRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $returnId = $context['args']['returnId'] ?? request()->query('return_id');

        $owned = $returnId && $this->rmaRepository
            ->whereHas('order', fn ($q) => $q->where('customer_id', $customer->id))
            ->find($returnId);

        if (! $owned) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.not-found'));
        }

        return $this->rmaMessageRepository
            ->where('rma_id', (int) $returnId)
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($message) => CustomerReturnMessage::fromModel($message))
            ->all();
    }
}
