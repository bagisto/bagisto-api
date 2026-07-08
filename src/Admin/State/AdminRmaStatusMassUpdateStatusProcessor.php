<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaStatusMassUpdateStatus;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\RMA\Repositories\RMAStatusRepository;

class AdminRmaStatusMassUpdateStatusProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly RMAStatusRepository $rmaStatusRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminRmaStatusMassUpdateStatus
    {
        $this->authorizedAdmin('sales.rma.statuses.edit', 'bagistoapi::app.admin.rma.no-permission');

        $indices = $data instanceof AdminRmaStatusMassUpdateStatusInput ? $data->indices : request()->input('indices');
        $value = $data instanceof AdminRmaStatusMassUpdateStatusInput ? $data->value : request()->input('value');

        if (empty($indices) || ! is_array($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.mass-indices-required'), 422);
        }

        if (! in_array((int) $value, [0, 1], true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.mass-value-invalid'), 422);
        }

        $updated = [];

        foreach ($indices as $id) {
            if ($this->rmaStatusRepository->find((int) $id)) {
                $this->rmaStatusRepository->update(['status' => (int) $value], (int) $id, ['status']);
                $updated[] = (int) $id;
            }
        }

        $result = new AdminRmaStatusMassUpdateStatus;
        $result->updated = $updated;
        $result->message = __('bagistoapi::app.admin.rma.mass-update-success');

        return $result;
    }
}
