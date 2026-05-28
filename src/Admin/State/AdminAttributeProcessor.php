<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
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

        // Detect GraphQL context via the operation type — $context['graphql_operation_name'] is not
        // reliably set by API Platform for 'create'/'update'/'delete' named mutations.
        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // ── GraphQL delete — must route before the update branch because both use AdminAttributeUpdateInput.
        // Detect by the operation name ('delete') rather than graphql_operation_name which AP doesn't always set.
        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminAttributeUpdateInput) {
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        // ── Create ────────────────────────────────────────────────────────────
        if ($data instanceof AdminAttributeCreateInput || ($data instanceof AdminAttribute && $operation instanceof Post)) {
            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        // ── Update ────────────────────────────────────────────────────────────
        if ($data instanceof AdminAttributeUpdateInput || ($data instanceof AdminAttribute && $operation instanceof Put)) {
            $id = (int) ($uriVariables['id'] ?? basename($this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        // ── REST Delete ───────────────────────────────────────────────────────
        if ($operation instanceof Delete) {
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleCreate(array $input): AdminAttribute
    {
        $rules = [
            'code'       => ['required', 'not_in:type,attribute_family_id', 'unique:attributes,code', new Code],
            'admin_name' => 'required',
            'type'       => ['required', 'in:'.implode(',', AttributeTypeEnum::getValues())],
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

        // Transform translations map → format expected by repository / Astrotomic TranslatableModel
        $options = $this->extractOptions($input);
        $inputData = $this->buildAttributeData($input, $options);

        Event::dispatch('catalog.attribute.create.before');

        $attribute = $this->attributeRepository->create($inputData);

        // Save attribute translations (locale-keyed map)
        $this->saveAttributeTranslations($attribute, $input['translations'] ?? []);

        Event::dispatch('catalog.attribute.create.after', $attribute);

        // Re-fetch as Eloquent model (repository returns the Contract interface which
        // doesn't declare load()); also picks up DB defaults like is_user_defined=1.
        $fresh = Attribute::with(['translations', 'options.translations'])->find($attribute->id);

        return $this->itemProvider->mapToDtoPublic($fresh);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Update
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleUpdate(int $id, array $input): AdminAttribute
    {
        $attribute = Attribute::find($id);
        if (! $attribute) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.not-found'));
        }

        $rules = [
            'code'       => ['required', new Code, "unique:attributes,code,{$id}"],
            'admin_name' => 'required',
            'type'       => ['required', 'in:'.implode(',', AttributeTypeEnum::getValues())],
        ];

        if (($input['type'] ?? '') === 'boolean') {
            $rules['default_value'] = 'nullable|in:0,1';
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            $first = $v->errors()->first();
            throw new InvalidInputException($first, 422);
        }

        // Refuse code change
        if (isset($input['code']) && $input['code'] !== $attribute->code) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.attribute.code-immutable'),
                422,
            );
        }

        // Refuse type change if product attribute values exist
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

        // Refuse value_per_locale change when product values exist
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
        unset($updateData['options']); // we handle options manually

        Event::dispatch('catalog.attribute.update.before', $id);

        DB::transaction(function () use ($attribute, $id, $updateData, $optionsPayload, $input) {
            $this->attributeRepository->update($updateData, $id);

            // Replace options if the type supports them and the caller sent options
            $optionTypes = [
                AttributeTypeEnum::SELECT->value,
                AttributeTypeEnum::MULTISELECT->value,
                AttributeTypeEnum::CHECKBOX->value,
            ];

            if ($optionsPayload !== null && in_array($attribute->type, $optionTypes)) {
                $this->replaceOptions($attribute, $optionsPayload);
            }

            // Save translations
            $this->saveAttributeTranslations($attribute, $input['translations'] ?? []);
        });

        Event::dispatch('catalog.attribute.update.after', Attribute::find($id));

        // Re-fetch fresh Eloquent model with relations
        $fresh = Attribute::with(['translations', 'options.translations'])->find($id);

        return $this->itemProvider->mapToDtoPublic($fresh);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleDelete(int $id): array
    {
        $attribute = Attribute::find($id);
        if (! $attribute) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.not-found'));
        }

        // Refuse system attributes
        if (! $attribute->is_user_defined) {
            throw new AuthorizationException(__('bagistoapi::app.admin.attribute.system-attribute'));
        }

        // Refuse if referenced in attribute families (attribute_group_mappings)
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

        try {
            Event::dispatch('catalog.attribute.delete.before', $id);

            $this->attributeRepository->delete($id);

            Event::dispatch('catalog.attribute.delete.after', $id);
        } catch (\Throwable $e) {
            // Repository::delete may throw if DB constraints block it
            throw new InvalidInputException(
                __('bagistoapi::app.admin.attribute.delete-failed'),
                500,
            );
        }

        return ['message' => __('bagistoapi::app.admin.attribute.delete-success')];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

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
                'admin_name'   => $opt['admin_name'] ?? '',
                'sort_order'   => $opt['sort_order'] ?? 0,
                'swatch_value' => $opt['swatch_value'] ?? null,
            ];

            // Translations: locale → label rows
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

            // Use translateOrNew provided by TranslatableModel
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

        // Delete options not in the incoming list
        foreach ($toDeleteIds as $optId) {
            $this->attributeOptionRepository->delete($optId);
        }

        foreach ($optionsPayload as $opt) {
            $optId = $opt['id'] ?? null;

            $optData = [
                'attribute_id' => $attribute->id,
                'admin_name'   => $opt['admin_name'] ?? '',
                'sort_order'   => $opt['sort_order'] ?? 0,
                'swatch_value' => $opt['swatch_value'] ?? null,
            ];

            // Flatten translations into locale-keyed sub-arrays
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

    // ─────────────────────────────────────────────────────────────────────────
    // Input resolution
    // ─────────────────────────────────────────────────────────────────────────

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        // For GraphQL mutations, args are at $context['args']['input'] (camelCase keys).
        // For REST POST, API Platform denormalizes body → AdminAttributeCreateInput DTO
        // but the name converter maps snake_case JSON keys to camelCase on the DTO
        // (which has snake_case properties), causing them to be lost. Read from
        // request()->all() directly for REST.
        if ($isGraphQL && $data instanceof AdminAttributeCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        // REST POST — raw request body is always authoritative
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

        // REST PUT — raw request body
        return request()->all();
    }

    /**
     * Convert a DTO to an array, preferring raw GraphQL args for multi-word
     * snake_case fields that the name converter may have mangled.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        // Start with raw args (camelCase keys from GraphQL)
        $result = [];

        // Map known camelCase → snake_case field names from GraphQL args
        $camelToSnake = [
            'adminName'        => 'admin_name',
            'swatchType'       => 'swatch_type',
            'isRequired'       => 'is_required',
            'isUnique'         => 'is_unique',
            'isFilterable'     => 'is_filterable',
            'isConfigurable'   => 'is_configurable',
            'isVisibleOnFront' => 'is_visible_on_front',
            'isComparable'     => 'is_comparable',
            'valuePerLocale'   => 'value_per_locale',
            'valuePerChannel'  => 'value_per_channel',
            'enableWysiwyg'    => 'enable_wysiwyg',
            'defaultValue'     => 'default_value',
        ];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $snakeKey = $camelToSnake[$key] ?? $key;
            $result[$snakeKey] = $value;
        }

        // Fill in snake_case DTO properties that were actually set
        foreach (get_object_vars($dto) as $key => $value) {
            if ($value !== null && ! array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
