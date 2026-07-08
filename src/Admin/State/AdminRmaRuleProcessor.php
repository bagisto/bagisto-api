<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleUpdateInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaRule;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Repositories\RMARuleRepository;

class AdminRmaRuleProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMARuleRepository $rmaRuleRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof AdminRmaRuleCreateInput) {
            return $this->handleCreate($data);
        }

        if ($data instanceof AdminRmaRuleUpdateInput) {
            $id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : (int) basename((string) $data->id);

            if ($operation instanceof Mutation && $operation->getName() === 'delete') {
                return $this->handleDelete($id);
            }

            return $this->handleUpdate($id, $data);
        }

        if ($data instanceof AdminRmaRule && $operation instanceof Delete) {
            return $this->handleDelete((int) ($uriVariables['id'] ?? $data->id));
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleCreate(AdminRmaRuleCreateInput $input): AdminRmaRule
    {
        $this->authorizedAdmin('sales.rma.rules.create', 'bagistoapi::app.admin.rma.no-permission');

        $this->validatePayload(['name' => $input->name, 'status' => $input->status, 'description' => $input->description]);

        Event::dispatch('sales.rma.rules.create.before');

        $rule = $this->rmaRuleRepository->create([
            'name'          => $input->name,
            'status'        => $input->status,
            'description'   => $input->description,
            'return_period' => $input->return_period,
        ]);

        Event::dispatch('sales.rma.rules.create.after', $rule);

        return $this->map((int) $rule->id);
    }

    private function handleUpdate(int $id, AdminRmaRuleUpdateInput $input): AdminRmaRule
    {
        $this->authorizedAdmin('sales.rma.rules.edit', 'bagistoapi::app.admin.rma.no-permission');

        if (! $this->rmaRuleRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.rule-not-found'));
        }

        $this->validatePayload(['name' => $input->name, 'status' => $input->status, 'description' => $input->description]);

        Event::dispatch('sales.rma.rules.update.before', $id);

        $rule = $this->rmaRuleRepository->update([
            'name'          => $input->name,
            'status'        => $input->status,
            'description'   => $input->description,
            'return_period' => $input->return_period,
        ], $id);

        Event::dispatch('sales.rma.rules.update.after', $rule);

        return $this->map($id);
    }

    private function handleDelete(int $id): AdminRmaRule
    {
        $this->authorizedAdmin('sales.rma.rules.delete', 'bagistoapi::app.admin.rma.no-permission');

        if (! $this->rmaRuleRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.rule-not-found'));
        }

        $snapshot = $this->map($id);

        Event::dispatch('sales.rma.rules.delete.before', $id);

        $this->rmaRuleRepository->delete($id);

        Event::dispatch('sales.rma.rules.delete.after', $id);

        $snapshot->message = __('bagistoapi::app.admin.rma.rule-deleted');

        return $snapshot;
    }

    private function validatePayload(array $data): void
    {
        $validator = Validator::make($data, [
            'name'        => 'required|string',
            'status'      => 'required|boolean',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }

    private function map(int $id): AdminRmaRule
    {
        return AdminRmaRuleItemProvider::toDto(DB::table('rma_rules')->where('id', $id)->first());
    }
}
