<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonMassDeleteInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaReasonMassDelete;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\RMA\Repositories\RMAReasonRepository;
use Webkul\RMA\Repositories\RMAReasonResolutionRepository;

class AdminRmaReasonMassDeleteProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly RMAReasonRepository $rmaReasonRepository,
        private readonly RMAReasonResolutionRepository $rmaReasonResolutionRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminRmaReasonMassDelete
    {
        $this->authorizedAdmin('sales.rma.reasons.delete', 'bagistoapi::app.admin.rma.no-permission');

        $indices = $data instanceof AdminRmaReasonMassDeleteInput ? $data->indices : request()->input('indices');

        if (empty($indices) || ! is_array($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.mass-indices-required'), 422);
        }

        $deleted = [];

        foreach ($indices as $id) {
            if ($this->rmaReasonRepository->find((int) $id)) {
                $this->rmaReasonResolutionRepository->where('rma_reason_id', (int) $id)->delete();
                $this->rmaReasonRepository->delete((int) $id);
                $deleted[] = (int) $id;
            }
        }

        $result = new AdminRmaReasonMassDelete;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.rma.mass-delete-success');

        return $result;
    }
}
