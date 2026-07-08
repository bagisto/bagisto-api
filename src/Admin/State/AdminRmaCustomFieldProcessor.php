<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminRmaCustomFieldCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaCustomFieldUpdateInput;
use Webkul\BagistoApi\Admin\Models\AdminRmaCustomField;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Repositories\RMACustomFieldOptionRepository;
use Webkul\RMA\Repositories\RMACustomFieldRepository;

class AdminRmaCustomFieldProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    private const TYPES = ['text', 'textarea', 'select', 'multiselect', 'checkbox', 'radio', 'date'];

    private const OPTION_TYPES = ['select', 'multiselect', 'checkbox', 'radio'];

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMACustomFieldRepository $rmaCustomFieldRepository,
        private readonly RMACustomFieldOptionRepository $rmaCustomFieldOptionRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof AdminRmaCustomFieldCreateInput) {
            return $this->handleCreate($data);
        }

        if ($data instanceof AdminRmaCustomFieldUpdateInput) {
            $id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : (int) basename((string) $data->id);

            if ($operation instanceof Mutation && $operation->getName() === 'delete') {
                return $this->handleDelete($id);
            }

            return $this->handleUpdate($id, $data);
        }

        if ($data instanceof AdminRmaCustomField && $operation instanceof Delete) {
            return $this->handleDelete((int) ($uriVariables['id'] ?? $data->id));
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleCreate(AdminRmaCustomFieldCreateInput $input): AdminRmaCustomField
    {
        $this->authorizedAdmin('sales.rma.custom-fields.create', 'bagistoapi::app.admin.rma.no-permission');

        $this->validatePayload($input->code, $input->label, $input->position, $input->type, $input->options, 'unique:rma_custom_fields,code');

        Event::dispatch('sales.rma.custom-field.create.before');

        $field = $this->rmaCustomFieldRepository->create([
            'label'            => $input->label,
            'code'             => $input->code,
            'position'         => $input->position,
            'type'             => $input->type,
            'is_required'      => $input->is_required ?? 0,
            'input_validation' => $input->input_validation,
            'status'           => $input->status ?? 0,
        ]);

        $this->syncOptions((int) $field->id, $input->type, $input->options);

        Event::dispatch('sales.rma.custom-field.create.after', $field);

        return $this->map((int) $field->id);
    }

    private function handleUpdate(int $id, AdminRmaCustomFieldUpdateInput $input): AdminRmaCustomField
    {
        $this->authorizedAdmin('sales.rma.custom-fields.edit', 'bagistoapi::app.admin.rma.no-permission');

        if (! $this->rmaCustomFieldRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.custom-field-not-found'));
        }

        $this->validatePayload($input->code, $input->label, $input->position, $input->type, $input->options, 'unique:rma_custom_fields,code,'.$id);

        Event::dispatch('sales.rma.custom-field.update.before', $id);

        $field = $this->rmaCustomFieldRepository->update([
            'label'            => $input->label,
            'code'             => $input->code,
            'position'         => $input->position,
            'type'             => $input->type,
            'is_required'      => $input->is_required ?? 0,
            'input_validation' => $input->input_validation,
            'status'           => $input->status ?? 0,
        ], $id);

        $this->rmaCustomFieldOptionRepository->where('rma_custom_field_id', $id)->delete();

        $this->syncOptions($id, $input->type, $input->options);

        Event::dispatch('sales.rma.custom-field.update.after', $field);

        return $this->map($id);
    }

    private function handleDelete(int $id): AdminRmaCustomField
    {
        $this->authorizedAdmin('sales.rma.custom-fields.delete', 'bagistoapi::app.admin.rma.no-permission');

        if (! $this->rmaCustomFieldRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.custom-field-not-found'));
        }

        $snapshot = $this->map($id);

        Event::dispatch('sales.rma.custom-field.delete.before', $id);

        $this->rmaCustomFieldRepository->delete($id);

        Event::dispatch('sales.rma.custom-field.delete.after', $id);

        $snapshot->message = __('bagistoapi::app.admin.rma.custom-field-deleted');

        return $snapshot;
    }

    private function syncOptions(int $fieldId, ?string $type, ?array $options): void
    {
        if (! in_array($type, self::OPTION_TYPES, true) || empty($options)) {
            return;
        }

        $this->rmaCustomFieldOptionRepository->createOption([
            'options' => array_map(fn ($o) => $o['name'] ?? null, $options),
            'value'   => array_map(fn ($o) => $o['value'] ?? null, $options),
        ], $fieldId);
    }

    private function validatePayload(?string $code, ?string $label, ?int $position, ?string $type, ?array $options, string $codeUniqueRule): void
    {
        $validator = Validator::make([
            'label'    => $label,
            'code'     => $code,
            'position' => $position,
            'type'     => $type,
            'options'  => $options,
        ], [
            'label'    => 'required',
            'code'     => 'required|'.$codeUniqueRule,
            'position' => 'required',
            'type'     => 'required|in:'.implode(',', self::TYPES),
            'options'  => 'required_if:type,'.implode(',', self::OPTION_TYPES).'|nullable|array',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        if (in_array($type, self::OPTION_TYPES, true) && count($options ?? []) < 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.custom-field-options-required'), 422);
        }
    }

    private function map(int $id): AdminRmaCustomField
    {
        return AdminRmaCustomFieldItemProvider::toDto(DB::table('rma_custom_fields')->where('id', $id)->first(), true);
    }
}
