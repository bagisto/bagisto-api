<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusMassDeleteInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaStatusMassDelete;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\RMA\Repositories\RMAStatusRepository;

class AdminRmaStatusMassDeleteProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly RMAStatusRepository $rmaStatusRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminRmaStatusMassDelete
    {
        $this->authorizedAdmin('sales.rma.statuses.delete', 'bagistoapi::app.admin.rma.no-permission');

        $indices = $data instanceof AdminRmaStatusMassDeleteInput ? $data->indices : request()->input('indices');

        if (empty($indices) || ! is_array($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.mass-indices-required'), 422);
        }

        $deleted = [];

        foreach ($indices as $id) {
            $status = $this->rmaStatusRepository->find((int) $id);

            if ($status && (int) $status->default === 0) {
                $this->rmaStatusRepository->where('default', 0)->delete((int) $id);
                $deleted[] = (int) $id;
            }
        }

        $result = new AdminRmaStatusMassDelete;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.rma.mass-delete-success');

        return $result;
    }
}
