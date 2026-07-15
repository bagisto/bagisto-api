<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Attribute\Enums\AttributeTypeEnum;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminAttribute;
use Webkul\BagistoApi\Admin\Models\AdminAttributeOption;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

/**
 * Handles POST, PUT, DELETE on AdminAttributeOption sub-resource.
 *
 * POST   /api/admin/catalog/attributes/{attributeId}/options
 * PUT    /api/admin/catalog/attributes/{attributeId}/options/{optionId}
 * DELETE /api/admin/catalog/attributes/{attributeId}/options/{optionId}
 * + matching GraphQL mutations.
 */
class AdminAttributeOptionProcessor implements ProcessorInterface
{
    /** Types that support options. */
    private const OPTION_TYPES = [
        AttributeTypeEnum::SELECT->value,
        AttributeTypeEnum::MULTISELECT->value,
        AttributeTypeEnum::CHECKBOX->value,
    ];

    public function __construct(
        protected AttributeOptionRepository $attributeOptionRepository,
        protected AdminAttributeItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if ($operation instanceof Put) {
            $attributeId = $this->resolveAttributeId($uriVariables, $context, $data);
            $optionId = $this->resolveOptionId($uriVariables, $context, $data);
            $input = $this->resolveInput($data, $context);

            return $this->handleUpdate($attributeId, $optionId, $input);
        }

        if ($operation instanceof Delete) {
            $attributeId = $this->resolveAttributeId($uriVariables, $context, $data);
            $optionId = $this->resolveOptionId($uriVariables, $context, $data);

            return $this->handleDelete($attributeId, $optionId);
        }

        if ($operation instanceof Mutation && $operation->getName() === 'delete') {
            $attributeId = $this->resolveAttributeId($uriVariables, $context, $data);
            $optionId = $this->resolveOptionId($uriVariables, $context, $data);

            return $this->handleDelete($attributeId, $optionId, true);
        }

        if ($operation instanceof Post) {
            $attributeId = $this->resolveAttributeId($uriVariables, $context, $data);
            $input = $this->resolveInput($data, $context);

            return $this->handleCreate($attributeId, $input);
        }

        if ($operation instanceof Mutation) {
            $opName = $operation->getName();

            if ($opName === 'create') {
                $attributeId = $this->resolveAttributeId($uriVariables, $context, $data);
                $input = $this->resolveInput($data, $context);

                return $this->handleCreate($attributeId, $input, true);
            }

            if ($opName === 'update') {
                $attributeId = $this->resolveAttributeId($uriVariables, $context, $data);
                $optionId = $this->resolveOptionId($uriVariables, $context, $data);
                $input = $this->resolveInput($data, $context);

                return $this->handleUpdate($attributeId, $optionId, $input, true);
            }
        }

        return null;
    }

    protected function handleCreate(int $attributeId, array $input, bool $asOption = false): AdminAttribute|AdminAttributeOption
    {
        $attribute = Attribute::with(['translations', 'options.translations'])->find($attributeId);
        if (! $attribute) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.not-found'));
        }

        if (! in_array($attribute->type, self::OPTION_TYPES)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.attribute.option-not-supported', ['type' => $attribute->type]),
                422,
            );
        }

        $v = Validator::make($input, [
            'admin_name' => 'required',
        ]);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $optData = $this->buildOptionData($attributeId, $input);

        $option = $this->attributeOptionRepository->create($optData);

        Event::dispatch('catalog.attribute.update.before', $attributeId);
        Event::dispatch('catalog.attribute.update.after', $attribute);

        if ($asOption) {
            return $this->mapOption($option);
        }

        $attribute->load(['translations', 'options.translations']);

        return $this->itemProvider->mapToDtoPublic($attribute);
    }

    protected function handleUpdate(int $attributeId, int $optionId, array $input, bool $asOption = false): AdminAttribute|AdminAttributeOption
    {
        $attribute = Attribute::with(['translations', 'options.translations'])->find($attributeId);
        if (! $attribute) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.not-found'));
        }

        $option = AttributeOption::find($optionId);
        if (! $option || (int) $option->attribute_id !== $attributeId) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.option-not-found'));
        }

        $optData = $this->buildOptionData($attributeId, $input);

        $this->attributeOptionRepository->update($optData, $optionId);

        Event::dispatch('catalog.attribute.update.before', $attributeId);
        Event::dispatch('catalog.attribute.update.after', $attribute);

        if ($asOption) {
            return $this->mapOption(AttributeOption::find($optionId));
        }

        $attribute->load(['translations', 'options.translations']);

        return $this->itemProvider->mapToDtoPublic($attribute);
    }

    protected function handleDelete(int $attributeId, int $optionId, bool $asResource = false): array|AdminAttributeOption
    {
        $attribute = Attribute::find($attributeId);
        if (! $attribute) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.not-found'));
        }

        $option = AttributeOption::find($optionId);
        if (! $option || (int) $option->attribute_id !== $attributeId) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.option-not-found'));
        }

        $useCount = DB::table('product_attribute_values')
            ->where('attribute_id', $attributeId)
            ->where('integer_value', $optionId)
            ->count();

        if ($useCount > 0) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.attribute.option-in-use', ['count' => $useCount]),
                409,
            );
        }

        $snapshot = $this->mapOption($option);

        $this->attributeOptionRepository->delete($optionId);

        Event::dispatch('catalog.attribute.update.before', $attributeId);
        Event::dispatch('catalog.attribute.update.after', $attribute);

        if ($asResource) {
            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.attribute.option-delete-success')];
    }

    protected function mapOption(AttributeOption $option): AdminAttributeOption
    {
        $dto = new AdminAttributeOption;
        $dto->id = (int) $option->id;
        $dto->attribute_id = (int) $option->attribute_id;
        $dto->admin_name = $option->admin_name;
        $dto->sort_order = (int) ($option->sort_order ?? 0);
        $dto->swatch_value = $option->swatch_value;

        return $dto;
    }

    protected function buildOptionData(int $attributeId, array $input): array
    {
        $optData = [
            'attribute_id' => $attributeId,
        ];

        if (isset($input['admin_name'])) {
            $optData['admin_name'] = $input['admin_name'];
        }

        if (array_key_exists('sort_order', $input)) {
            $optData['sort_order'] = $input['sort_order'] ?? 0;
        }

        if (array_key_exists('swatch_value', $input)) {
            $optData['swatch_value'] = $input['swatch_value'];
        }

        if (! empty($input['translations'])) {
            foreach ($input['translations'] as $locale => $trans) {
                $optData[$locale] = ['label' => $trans['label'] ?? ''];
            }
        }

        return $optData;
    }

    protected function resolveAttributeId(array $uriVariables, array $context, mixed $data): int
    {
        $raw = $uriVariables['attributeId']
            ?? $context['args']['input']['attributeId']
            ?? $context['args']['attributeId']
            ?? request()->route('attributeId')
            ?? request()->input('attributeId')
            ?? null;

        return (int) ($raw ?? 0);
    }

    protected function resolveOptionId(array $uriVariables, array $context, mixed $data): int
    {
        $raw = $uriVariables['optionId']
            ?? $context['args']['input']['optionId']
            ?? $context['args']['optionId']
            ?? request()->route('optionId')
            ?? request()->input('optionId')
            ?? null;

        return (int) ($raw ?? 0);
    }

    protected function resolveInput(mixed $data, array $context): array
    {
        $args = $context['args']['input'] ?? $context['args'] ?? [];
        if (! empty($args) && is_array($args)) {
            unset($args['id'], $args['attributeId'], $args['optionId']);

            return $this->normaliseArgs($args);
        }

        return request()->except(['_method', '_token']);
    }

    /**
     * Map GraphQL camelCase input keys to the snake_case keys buildOptionData expects.
     */
    protected function normaliseArgs(array $args): array
    {
        $camelToSnake = [
            'adminName' => 'admin_name',
            'sortOrder' => 'sort_order',
            'swatchValue' => 'swatch_value',
        ];

        $result = [];

        foreach ($args as $key => $value) {
            $snakeKey = $camelToSnake[$key] ?? $key;
            $result[$snakeKey] = $value;
        }

        return $result;
    }
}
