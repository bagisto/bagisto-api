<?php

namespace Webkul\BagistoApi\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Delegates to the same logic as SnakeCaseToCamelCaseNameConverter
 * (which is final and cannot be extended).
 *
 * - normalize: snake_case → camelCase (output)
 * - denormalize: camelCase → snake_case (input/property reading)
 *
 * This works correctly for Eloquent models and GraphQL.
 * For REST DTOs with camelCase properties, processors read
 * directly from the request JSON body as a fallback.
 *
 * IMPORTANT — DTO property naming over GraphQL: API Platform resolves a GraphQL
 * field's VALUE by `denormalize()`-ing the camelCase field name to a property
 * name and reading that property. It calls this converter WITHOUT class context
 * (the class arg is null in that path), so the converter cannot special-case
 * individual DTOs. Consequence: output DTO properties MUST be snake_case
 * (`order_id`, not `orderId`) — snake properties round-trip correctly over both
 * REST and GraphQL and still surface as camelCase to clients. camelCase DTO
 * properties resolve over REST but come back null over GraphQL.
 */
class OutputOnlySnakeToCamelNameConverter implements NameConverterInterface
{
    public function normalize(string $propertyName): string
    {
        // Preserve leading underscores (e.g. _lft, _rgt, _id)
        $prefix = '';
        $name = $propertyName;
        if (str_starts_with($name, '_')) {
            $prefix = '_';
            $name = substr($name, 1);
        }

        return $prefix.lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
    }

    public function denormalize(string $propertyName): string
    {
        // Preserve leading underscores
        $prefix = '';
        $name = $propertyName;
        if (str_starts_with($name, '_')) {
            $prefix = '_';
            $name = substr($name, 1);
        }

        return $prefix.strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($name)));
    }
}
