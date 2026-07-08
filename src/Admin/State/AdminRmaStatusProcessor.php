<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusUpdateInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaStatus;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Repositories\RMAStatusRepository;

class AdminRmaStatusProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMAStatusRepository $rmaStatusRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof AdminRmaStatusCreateInput) {
            return $this->handleCreate($data);
        }

        if ($data instanceof AdminRmaStatusUpdateInput) {
            $id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : (int) basename((string) $data->id);

            if ($operation instanceof Mutation && $operation->getName() === 'delete') {
                return $this->handleDelete($id);
            }

            return $this->handleUpdate($id, $data);
        }

        if ($data instanceof AdminRmaStatus && $operation instanceof Delete) {
            return $this->handleDelete((int) ($uriVariables['id'] ?? $data->id));
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleCreate(AdminRmaStatusCreateInput $input): AdminRmaStatus
    {
        $this->authorizedAdmin('sales.rma.statuses.create', 'bagistoapi::app.admin.rma.no-permission');

        $this->validatePayload(['title' => $input->title, 'status' => $input->status], 'required|unique:rma_statuses,title');

        Event::dispatch('sales.rma.rma-status.create.before');

        $status = $this->rmaStatusRepository->create([
            'title'  => $input->title,
            'status' => $input->status,
            'color'  => $input->color,
        ]);

        Event::dispatch('sales.rma.rma-status.create.after', $status);

        return $this->map((int) $status->id);
    }

    private function handleUpdate(int $id, AdminRmaStatusUpdateInput $input): AdminRmaStatus
    {
        $this->authorizedAdmin('sales.rma.statuses.edit', 'bagistoapi::app.admin.rma.no-permission');

        if (! $this->rmaStatusRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.status-not-found'));
        }

        $this->validatePayload(['title' => $input->title, 'status' => $input->status], 'required|unique:rma_statuses,title,'.$id);

        Event::dispatch('sales.rma.rma-status.update.before', $id);

        $status = $this->rmaStatusRepository->update([
            'title'  => $input->title,
            'status' => $input->status,
            'color'  => $input->color,
        ], $id);

        Event::dispatch('sales.rma.rma-status.update.after', $status);

        return $this->map($id);
    }

    private function handleDelete(int $id): AdminRmaStatus
    {
        $this->authorizedAdmin('sales.rma.statuses.delete', 'bagistoapi::app.admin.rma.no-permission');

        $status = $this->rmaStatusRepository->find($id);

        if (! $status) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.status-not-found'));
        }

        if ((int) $status->default === 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.status-default-undeletable'), 422);
        }

        $snapshot = $this->map($id);

        Event::dispatch('sales.rma.rma-status.delete.before', $id);

        $this->rmaStatusRepository->where('default', 0)->delete($id);

        Event::dispatch('sales.rma.rma-status.delete.after', $id);

        $snapshot->message = __('bagistoapi::app.admin.rma.status-deleted');

        return $snapshot;
    }

    private function validatePayload(array $data, string $titleRule): void
    {
        $validator = Validator::make($data, [
            'title'  => $titleRule,
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }

    private function map(int $id): AdminRmaStatus
    {
        return AdminRmaStatusItemProvider::toDto(DB::table('rma_statuses')->where('id', $id)->first());
    }
}
