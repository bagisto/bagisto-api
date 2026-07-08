<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminRmaCustomFieldMassDeleteInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaCustomFieldMassDelete;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\RMA\Repositories\RMACustomFieldRepository;

class AdminRmaCustomFieldMassDeleteProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(private readonly RMACustomFieldRepository $rmaCustomFieldRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminRmaCustomFieldMassDelete
    {
        $this->authorizedAdmin('sales.rma.custom-fields.delete', 'bagistoapi::app.admin.rma.no-permission');

        $indices = $data instanceof AdminRmaCustomFieldMassDeleteInput ? $data->indices : request()->input('indices');

        if (empty($indices) || ! is_array($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.mass-indices-required'), 422);
        }

        $deleted = [];
        foreach ($indices as $id) {
            if ($this->rmaCustomFieldRepository->find((int) $id)) {
                $this->rmaCustomFieldRepository->delete((int) $id);
                $deleted[] = (int) $id;
            }
        }

        $result = new AdminRmaCustomFieldMassDelete;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.rma.mass-delete-success');

        return $result;
    }
}
