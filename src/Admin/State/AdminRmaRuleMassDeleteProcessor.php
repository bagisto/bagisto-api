<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleMassDeleteInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaRuleMassDelete;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\RMA\Repositories\RMARuleRepository;

class AdminRmaRuleMassDeleteProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(private readonly RMARuleRepository $rmaRuleRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminRmaRuleMassDelete
    {
        $this->authorizedAdmin('sales.rma.rules.delete', 'bagistoapi::app.admin.rma.no-permission');

        $indices = $data instanceof AdminRmaRuleMassDeleteInput ? $data->indices : request()->input('indices');

        if (empty($indices) || ! is_array($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.mass-indices-required'), 422);
        }

        $deleted = [];
        foreach ($indices as $id) {
            if ($this->rmaRuleRepository->find((int) $id)) {
                $this->rmaRuleRepository->delete((int) $id);
                $deleted[] = (int) $id;
            }
        }

        $result = new AdminRmaRuleMassDelete;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.rma.mass-delete-success');

        return $result;
    }
}
