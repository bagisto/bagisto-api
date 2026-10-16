<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\BagistoApi\Models\CompareAttribute;
use Webkul\BagistoApi\State\Concerns\StorefrontFeatureGate;

/**
 * Serves the comparable attributes the storefront compare page renders.
 */
class CompareAttributeProvider implements ProviderInterface
{
    use StorefrontFeatureGate;

    public function __construct(
        protected AttributeFamilyRepository $attributeFamilyRepository
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->assertCompareEnabled();

        return $this->attributeFamilyRepository
            ->getComparableAttributesBelongsToFamily()
            ->map(function ($attribute) {
                $item = new CompareAttribute;

                $item->id = (int) $attribute->id;
                $item->code = $attribute->code;
                $item->admin_name = $attribute->admin_name;
                $item->name = $attribute->name ?? $attribute->admin_name;
                $item->type = $attribute->type;

                return $item;
            })
            ->values()
            ->all();
    }
}
