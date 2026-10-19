<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\Operation;
use Illuminate\Database\Eloquent\Model;

class CustomIdentifiersExtractor implements IdentifiersExtractorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private IdentifiersExtractorInterface $decorated
    ) {}

    /**
     * Get identifiers from item.
     */
    public function getIdentifiersFromItem(object $item, ?Operation $operation = null, array $context = []): array
    {
        if ($item instanceof Model) {
            $id = null;

            if (
                method_exists($item, 'getId')
                && is_callable([$item, 'getId'])
            ) {
                $id = $item->getId();
            }

            if ($id === null) {
                $id = $item->getKey();
            }

            if ($id !== null) {
                return ['id' => $id];
            }

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

        $isClassMismatch = $operation?->getClass() !== null && $operation->getClass() !== $item::class;

        if (
            $isClassMismatch
            && property_exists($item, 'id')
            && $item->id !== null
        ) {
            return ['id' => $item->id];
        }

        return $identifiers;
    }
}
