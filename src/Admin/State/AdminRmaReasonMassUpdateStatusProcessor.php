<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaReasonMassUpdateStatus;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\RMA\Repositories\RMAReasonRepository;

class AdminRmaReasonMassUpdateStatusProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly RMAReasonRepository $rmaReasonRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminRmaReasonMassUpdateStatus
    {
        $this->authorizedAdmin('sales.rma.reasons.edit', 'bagistoapi::app.admin.rma.no-permission');

        $indices = $data instanceof AdminRmaReasonMassUpdateStatusInput ? $data->indices : request()->input('indices');
        $value = $data instanceof AdminRmaReasonMassUpdateStatusInput ? $data->value : request()->input('value');

        if (empty($indices) || ! is_array($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.mass-indices-required'), 422);
        }

        if (! in_array((int) $value, [0, 1], true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.mass-value-invalid'), 422);
        }

        $updated = [];

        foreach ($indices as $id) {
            if ($this->rmaReasonRepository->find((int) $id)) {
                $this->rmaReasonRepository->update(['status' => (int) $value], (int) $id, ['status']);
                $updated[] = (int) $id;
            }
        }

        $result = new AdminRmaReasonMassUpdateStatus;
        $result->updated = $updated;
        $result->message = __('bagistoapi::app.admin.rma.mass-update-success');

        return $result;
    }
}
