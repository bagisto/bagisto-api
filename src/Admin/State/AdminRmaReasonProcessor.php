<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonUpdateInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaReason;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Repositories\RMAReasonRepository;
use Webkul\RMA\Repositories\RMAReasonResolutionRepository;

class AdminRmaReasonProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    private const RESOLUTIONS = ['return', 'cancel_items'];

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMAReasonRepository $rmaReasonRepository,
        private readonly RMAReasonResolutionRepository $rmaReasonResolutionRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof AdminRmaReasonCreateInput) {
            return $this->handleCreate($data);
        }

        if ($data instanceof AdminRmaReasonUpdateInput) {
            $id = isset($uriVariables['id'])
                ? (int) $uriVariables['id']
                : (int) basename((string) $data->id);

            if ($operation instanceof Mutation && $operation->getName() === 'delete') {
                return $this->handleDelete($id);
            }

            return $this->handleUpdate($id, $data);
        }

        if ($data instanceof AdminRmaReason && $operation instanceof Delete) {
            return $this->handleDelete((int) ($uriVariables['id'] ?? $data->id));
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleCreate(AdminRmaReasonCreateInput $input): AdminRmaReason
    {
        $this->authorizedAdmin('sales.rma.reasons.create', 'bagistoapi::app.admin.rma.no-permission');

        $this->validatePayload([
            'title'           => $input->title,
            'status'          => $input->status,
            'position'        => $input->position,
            'resolution_type' => $input->resolution_type,
        ]);

        $reason = $this->rmaReasonRepository->create([
            'title'    => $input->title,
            'status'   => $input->status,
            'position' => $input->position,
        ]);

        foreach ($input->resolution_type as $resolutionType) {
            $this->rmaReasonResolutionRepository->create([
                'rma_reason_id'   => $reason->id,
                'resolution_type' => $resolutionType,
            ]);
        }

        return $this->mapReason((int) $reason->id);
    }

    private function handleUpdate(int $id, AdminRmaReasonUpdateInput $input): AdminRmaReason
    {
        $this->authorizedAdmin('sales.rma.reasons.edit', 'bagistoapi::app.admin.rma.no-permission');

        if (! $this->rmaReasonRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.reason-not-found'));
        }

        $this->validatePayload([
            'title'           => $input->title,
            'status'          => $input->status,
            'position'        => $input->position,
            'resolution_type' => $input->resolution_type,
        ]);

        $this->rmaReasonRepository->update([
            'title'    => $input->title,
            'status'   => $input->status,
            'position' => $input->position,
        ], $id);

        $resolutionTypes = $input->resolution_type;

        $this->rmaReasonResolutionRepository
            ->whereNotIn('resolution_type', $resolutionTypes)
            ->where('rma_reason_id', $id)
            ->delete();

        foreach ($resolutionTypes as $resolutionType) {
            $this->rmaReasonResolutionRepository->updateOrCreate([
                'rma_reason_id'   => $id,
                'resolution_type' => $resolutionType,
            ]);
        }

        return $this->mapReason($id);
    }

    private function handleDelete(int $id): AdminRmaReason
    {
        $this->authorizedAdmin('sales.rma.reasons.delete', 'bagistoapi::app.admin.rma.no-permission');

        if (! $this->rmaReasonRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.reason-not-found'));
        }

        $snapshot = $this->mapReason($id);

        $this->rmaReasonResolutionRepository->where('rma_reason_id', $id)->delete();
        $this->rmaReasonRepository->delete($id);

        $snapshot->message = __('bagistoapi::app.admin.rma.reason-deleted');

        return $snapshot;
    }

    private function validatePayload(array $data): void
    {
        $validator = Validator::make($data, [
            'title'             => 'required|string',
            'status'            => 'required|boolean',
            'position'          => 'required',
            'resolution_type'   => 'required|array|min:1',
            'resolution_type.*' => 'in:'.implode(',', self::RESOLUTIONS),
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }

    private function mapReason(int $id): AdminRmaReason
    {
        $row = DB::table('rma_reasons')->where('id', $id)->first();

        $dto = new AdminRmaReason;
        $dto->id = (int) $row->id;
        $dto->title = $row->title;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->position = $row->position !== null ? (int) $row->position : null;
        $dto->isAdmin = $row->is_admin !== null ? (int) $row->is_admin : null;
        $dto->resolutionType = DB::table('rma_reason_resolutions')->where('rma_reason_id', $id)->pluck('resolution_type')->all();
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
