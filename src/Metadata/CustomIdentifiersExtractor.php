<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\Operation;
use Illuminate\Database\Eloquent\Model;

/**
 * Extracts identifiers from Eloquent models for API Platform
 */
class CustomIdentifiersExtractor implements IdentifiersExtractorInterface
{
    public function __construct(
        private IdentifiersExtractorInterface $decorated
    ) {}

    public function getIdentifiersFromItem(object $item, ?Operation $operation = null, array $context = []): array
    {
        if ($item instanceof Model) {
            $id = null;

            if (method_exists($item, 'getId') && is_callable([$item, 'getId'])) {
                $id = $item->getId();
            }

            if ($id === null) {
                $id = $item->getKey();
            }

            // Only return identifier if it's not null
            if ($id !== null) {
                return ['id' => $id];
            }

            // For new items without an ID, return empty array to avoid IRI generation issues
            return [];
        }

        try {
            $identifiers = $this->decorated->getIdentifiersFromItem($item, $operation, $context);
        } catch (\Throwable) {
            $identifiers = [];
        }

        if (($identifiers['id'] ?? null) !== null) {
            return $identifiers;
        }

        // A provider returning a class other than its operation's yields no identifier — read the item's own id.
        $isClassMismatch = $operation?->getClass() !== null && $operation->getClass() !== $item::class;

        if ($isClassMismatch && property_exists($item, 'id') && $item->id !== null) {
            return ['id' => $item->id];
        }

        return $identifiers;
    }
}
