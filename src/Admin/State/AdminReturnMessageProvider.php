<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminReturnMessage;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;

class AdminReturnMessageProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly RMARepository $rmaRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $this->authorizedAdmin('sales.rma.requests', 'bagistoapi::app.admin.rma.no-permission');

        $returnId = $context['args']['returnId'] ?? request()->query('return_id');

        if (! $returnId || ! $this->rmaRepository->find($returnId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.not-found'));
        }

        return $this->rmaMessageRepository
            ->where('rma_id', (int) $returnId)
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($message) => AdminReturnMessage::fromModel($message))
            ->all();
    }
}
