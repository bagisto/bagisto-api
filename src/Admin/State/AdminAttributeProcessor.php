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
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminAttribute;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Rules\Code;

/**
 * Handles POST, PUT, DELETE on AdminAttribute resource.
 *
 * Routes by DTO type / Operation instance:
 *   - AdminAttributeCreateInput → handleCreate()
 *   - AdminAttribute (REST POST body)  → handleCreate()
 *   - AdminAttributeUpdateInput → handleUpdate()
 *   - AdminAttribute (REST PUT body)   → handleUpdate()
 *   - Delete op → handleDelete()
 */
class AdminAttributeProcessor implements ProcessorInterface
{
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeOptionRepository $attributeOptionRepository,
        protected AdminAttributeItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminAttributeUpdateInput) {
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminAttributeCreateInput || ($data instanceof AdminAttribute && $operation instanceof Post)) {
            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminAttributeUpdateInput || ($data instanceof AdminAttribute && $operation instanceof Put)) {
            $id = (int) ($uriVariables['id'] ?? basename($this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminAttribute
    {
        $rules = [
            'code' => ['required', 'not_in:type,attribute_family_id', 'unique:attributes,code', new Code],
            'admin_name' => 'required',
            'type' => ['required', 'in:'.implode(',', AttributeTypeEnum::getValues())],
        ];

        if (($input['type'] ?? '') === 'boolean') {
            $rules['default_value'] = 'nullable|in:0,1';
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            $first = $v->errors()->first();
            throw new InvalidInputException($first, 422);
        }

        $input['default_value'] ??= null;

        $options = $this->extractOptions($input);
        $inputData = $this->buildAttributeData($input, $options);

        Event::dispatch('catalog.attribute.create.before');

        $attribute = $this->attributeRepository->create($inputData);

        $this->saveAttributeTranslations($attribute, $input['translations'] ?? []);

        Event::dispatch('catalog.attribute.create.after', $attribute);

        $fresh = Attribute::with(['translations', 'options.translations'])->find($attribute->id);

        return $this->itemProvider->mapToDtoPublic($fresh);
    }

    protected function handleUpdate(int $id, array $input): AdminAttribute
    {
        $attribute = Attribute::find($id);
        if (! $attribute) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.not-found'));
        }

        $rules = [
            'code' => ['required', new Code, "unique:attributes,code,{$id}"],
            'admin_name' => 'required',
            'type' => ['required', 'in:'.implode(',', AttributeTypeEnum::getValues())],
        ];

        if (($input['type'] ?? '') === 'boolean') {
            $rules['default_value'] = 'nullable|in:0,1';
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            $first = $v->errors()->first();
            throw new InvalidInputException($first, 422);
        }

        if (isset($input['code']) && $input['code'] !== $attribute->code) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.attribute.code-immutable'),
                422,
            );
        }

        if (isset($input['type']) && $input['type'] !== $attribute->type) {
            $valueCount = DB::table('product_attribute_values')
                ->where('attribute_id', $id)
                ->count();
            if ($valueCount > 0) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.attribute.type-immutable'),
                    422,
                );
            }
        }

        if (isset($input['value_per_locale']) && (int) $input['value_per_locale'] !== (int) $attribute->value_per_locale) {
            $valueCount = DB::table('product_attribute_values')
                ->where('attribute_id', $id)
                ->count();
            if ($valueCount > 0) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.attribute.locale-scope-immutable'),
                    422,
                );
            }
        }

        $input['default_value'] ??= null;

        $optionsPayload = $input['options'] ?? null;
        $updateData = $this->buildAttributeData($input, null);
        unset($updateData['options']);

        Event::dispatch('catalog.attribute.update.before', $id);

        DB::transaction(function () use ($attribute, $id, $updateData, $optionsPayload, $input) {
            $this->attributeRepository->update($updateData, $id);

            $optionTypes = [
                AttributeTypeEnum::SELECT->value,
                AttributeTypeEnum::MULTISELECT->value,
                AttributeTypeEnum::CHECKBOX->value,
            ];

            if ($optionsPayload !== null && in_array($attribute->type, $optionTypes)) {
                $this->replaceOptions($attribute, $optionsPayload);
            }

            $this->saveAttributeTranslations($attribute, $input['translations'] ?? []);
        });

        Event::dispatch('catalog.attribute.update.after', Attribute::find($id));

        $fresh = Attribute::with(['translations', 'options.translations'])->find($id);

        return $this->itemProvider->mapToDtoPublic($fresh);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminAttribute
    {
        $attribute = Attribute::with(['translations', 'options.translations'])->find($id);
        if (! $attribute) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.not-found'));
        }

        if (! $attribute->is_user_defined) {
            throw new AuthorizationException(__('bagistoapi::app.admin.attribute.system-attribute'));
        }

        $familyIds = DB::table('attribute_group_mappings')
            ->where('attribute_id', $id)
            ->pluck('attribute_group_id')
            ->unique()
            ->values()
            ->all();

        if (! empty($familyIds)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.attribute.in-use-family', ['ids' => implode(', ', $familyIds)]),
                409,
            );
        }

        $snapshot = $asResource ? $this->itemProvider->mapToDtoPublic($attribute) : null;

        try {
            Event::dispatch('catalog.attribute.delete.before', $id);

            $this->attributeRepository->delete($id);

            Event::dispatch('catalog.attribute.delete.after', $id);
        } catch (\Throwable $e) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.attribute.delete-failed'),
                500,
            );
        }

        if ($asResource) {
            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.attribute.delete-success')];
    }

    /**
     * Build the flat attribute data array from the input array.
     * 'translations' and 'options' are handled separately.
     */
    protected function buildAttributeData(array $input, ?array $options): array
    {
        $data = [];

        $fields = [
            'code', 'admin_name', 'type', 'swatch_type', 'is_required', 'is_unique',
            'is_filterable', 'is_configurable', 'is_visible_on_front', 'is_comparable',
            'value_per_locale', 'value_per_channel', 'enable_wysiwyg', 'validation',
            'regex', 'default_value', 'position',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $input)) {
                $data[$field] = $input[$field];
            }
        }

        if ($options !== null) {
            $data['options'] = $options;
        }

        return $data;
    }

    /**
     * Extract and normalize the options array from the input.
     * For create: returns array ready for repository.
     */
    protected function extractOptions(array $input): array
    {
        if (empty($input['options'])) {
            return [];
        }

        $result = [];

        foreach ($input['options'] as $opt) {
            $optData = [
                'admin_name' => $opt['admin_name'] ?? '',
                'sort_order' => $opt['sort_order'] ?? 0,
                'swatch_value' => $opt['swatch_value'] ?? null,
            ];

            if (! empty($opt['translations'])) {
                foreach ($opt['translations'] as $locale => $trans) {
                    $optData[$locale] = ['label' => $trans['label'] ?? ''];
                }
            }

            $result[] = $optData;
        }

        return $result;
    }

    /**
     * Save attribute translations (locale-keyed map).
     */
    protected function saveAttributeTranslations(Attribute $attribute, array $translationsMap): void
    {
        if (empty($translationsMap)) {
            return;
        }

        foreach ($translationsMap as $locale => $trans) {
            $name = $trans['name'] ?? null;
            if ($name === null) {
                continue;
            }

            $translation = $attribute->translateOrNew($locale);
            $translation->name = $name;
            $translation->save();
        }
    }

    /**
     * Replace options for an attribute atomically.
     * Options with 'id' are updated; without 'id' are inserted; omitted ones are deleted.
     */
    protected function replaceOptions(Attribute $attribute, array $optionsPayload): void
    {
        $existingIds = $attribute->options()->pluck('id')->all();
        $incomingIds = array_filter(array_column($optionsPayload, 'id'));
        $toDeleteIds = array_diff($existingIds, $incomingIds);

        foreach ($toDeleteIds as $optId) {
            $this->attributeOptionRepository->delete($optId);
        }

        foreach ($optionsPayload as $opt) {
            $optId = $opt['id'] ?? null;

            $optData = [
                'attribute_id' => $attribute->id,
                'admin_name' => $opt['admin_name'] ?? '',
                'sort_order' => $opt['sort_order'] ?? 0,
                'swatch_value' => $opt['swatch_value'] ?? null,
            ];

            if (! empty($opt['translations'])) {
                foreach ($opt['translations'] as $locale => $trans) {
                    $optData[$locale] = ['label' => $trans['label'] ?? ''];
                }
            }

            if ($optId) {
                $this->attributeOptionRepository->update($optData, $optId);
            } else {
                $this->attributeOptionRepository->create($optData);
            }
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminAttributeCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): string
    {
        if ($data instanceof AdminAttributeUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminAttributeUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Convert a DTO to an array, preferring raw GraphQL args for multi-word
     * snake_case fields that the name converter may have mangled.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'adminName' => 'admin_name',
            'swatchType' => 'swatch_type',
            'isRequired' => 'is_required',
            'isUnique' => 'is_unique',
            'isFilterable' => 'is_filterable',
            'isConfigurable' => 'is_configurable',
            'isVisibleOnFront' => 'is_visible_on_front',
            'isComparable' => 'is_comparable',
            'valuePerLocale' => 'value_per_locale',
            'valuePerChannel' => 'value_per_channel',
            'enableWysiwyg' => 'enable_wysiwyg',
            'defaultValue' => 'default_value',
        ];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $snakeKey = $camelToSnake[$key] ?? $key;
            $result[$snakeKey] = $value;
        }

        foreach (get_object_vars($dto) as $key => $value) {
            if ($value !== null && ! array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
