<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminRefund;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminRefund;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Refund;

class AdminRefundProvider implements ProviderInterface
{
    use BuildsAdminRefund;

    public function __construct(protected AdminOrderActionGuard $guard) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminRefund
    {
        $this->guard->resolveAdmin();

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.refund.not-found'));
        }

        $refund = Refund::with([
            'items',
            'items.product',
            'order',
            'order.addresses',
            'order.payment',
        ])->find($id);

        if (! $refund) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.refund.not-found'));
        }

        return $this->buildAdminRefund($refund);
    }
}
