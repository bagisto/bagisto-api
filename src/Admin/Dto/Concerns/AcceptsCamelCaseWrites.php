<?php

namespace Webkul\BagistoApi\Admin\Dto\Concerns;

/**
 * Output DTO helper for the admin API.
 *
 * API Platform's GraphQL value-read resolves a field by snake-casing the
 * camelCase field name (`orderId` → `order_id`) and reading THAT property off
 * the object via reflection, with no class context. So output DTO properties
 * MUST be declared snake_case (`public ?int $order_id`) to resolve over both
 * REST and GraphQL — they still surface as camelCase to clients via the name
 * converter.
 *
 * Providers, however, were written to assign camelCase (`$dto->orderId = …`).
 * This trait's `__set` maps those camelCase writes onto the matching snake_case
 * property, so the DTOs can switch to snake_case without touching any provider.
 * `__set` fires only for writes to a property that is not declared/visible, so
 * it never interferes with real snake_case properties.
 */
trait AcceptsCamelCaseWrites
{
    public function __set(string $name, mixed $value): void
    {
        $snake = strtolower((string) preg_replace('/[A-Z]/', '_$0', lcfirst($name)));

        if ($snake !== $name && property_exists($this, $snake)) {
            $this->{$snake} = $value;

            return;
        }

        // Unknown property — preserve default PHP behaviour (dynamic property).
        $this->{$name} = $value;
    }
}
