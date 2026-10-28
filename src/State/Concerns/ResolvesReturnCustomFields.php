<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Collection;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\RMA\Repositories\RMACustomFieldRepository;

trait ResolvesReturnCustomFields
{
    public const OPTION_FIELD_TYPES = ['select', 'radio'];

    public const MULTI_OPTION_FIELD_TYPES = ['multiselect', 'checkbox'];

    protected function activeReturnCustomFields(RMACustomFieldRepository $repository): Collection
    {
        return $repository
            ->with('options')
            ->where('status', 1)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<int|string,mixed>  $customAttributes
     * @return array<int,mixed>
     */
    protected function validateReturnCustomAttributes(
        array $customAttributes,
        RMACustomFieldRepository $repository
    ): array {
        $fields = $this->activeReturnCustomFields($repository);

        $answers = [];

        foreach ($customAttributes as $fieldId => $value) {
            $field = $fields->firstWhere('id', (int) $fieldId);

            if (! $field) {
                throw new InvalidInputException(
                    __('bagistoapi::app.graphql.return.invalid-custom-field', ['id' => $fieldId])
                );
            }

            $answers[(int) $field->id] = $this->normalizeCustomFieldValue($field, $value);
        }

        foreach ($fields as $field) {
            if (
                (int) $field->is_required === 1
                && $this->isEmptyCustomFieldValue($answers[(int) $field->id] ?? null)
            ) {
                throw new InvalidInputException(
                    __('bagistoapi::app.graphql.return.custom-field-required', ['field' => $field->label ?? $field->code])
                );
            }
        }

        return array_filter($answers, fn ($value) => ! $this->isEmptyCustomFieldValue($value));
    }

    private function normalizeCustomFieldValue($field, mixed $value): mixed
    {
        if (in_array($field->type, self::MULTI_OPTION_FIELD_TYPES, true)) {
            $values = is_array($value)
                ? $value
                : array_filter(array_map('trim', explode(',', (string) $value)), fn ($single) => $single !== '');

            foreach ($values as $single) {
                $this->assertAllowedOption($field, $single);
            }

            return array_values($values);
        }

        if (is_array($value)) {
            throw new InvalidInputException(
                __('bagistoapi::app.graphql.return.invalid-custom-field-value', ['field' => $field->label ?? $field->code])
            );
        }

        if (
            in_array($field->type, self::OPTION_FIELD_TYPES, true)
            && $value !== null
            && $value !== ''
        ) {
            $this->assertAllowedOption($field, $value);
        }

        return $value === null ? null : (string) $value;
    }

    private function assertAllowedOption($field, mixed $value): void
    {
        $allowed = $field->options->pluck('value')->map(fn ($option) => (string) $option)->all();

        if (! in_array((string) $value, $allowed, true)) {
            throw new InvalidInputException(
                __('bagistoapi::app.graphql.return.invalid-custom-field-value', ['field' => $field->label ?? $field->code])
            );
        }
    }

    private function isEmptyCustomFieldValue(mixed $value): bool
    {
        if (is_array($value)) {
            return $value === [];
        }

        return $value === null || trim((string) $value) === '';
    }
}
