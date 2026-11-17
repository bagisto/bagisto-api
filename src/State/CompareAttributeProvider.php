<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\BagistoApi\Models\CompareAttribute;

class CompareAttributeProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected AttributeFamilyRepository $attributeFamilyRepository
    ) {}

    /**
     * Provide the comparable attributes the storefront compare page renders.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->attributeFamilyRepository
            ->getComparableAttributesBelongsToFamily()
            ->map(fn ($attribute) => $this->toResource($attribute))
            ->values()
            ->all();
    }

    /**
     * Map an attribute to its resource.
     */
    protected function toResource(object $attribute): CompareAttribute
    {
        $item = new CompareAttribute;

        $item->id = (int) $attribute->id;
        $item->code = $attribute->code;
        $item->admin_name = $attribute->admin_name;
        $item->name = $attribute->name ?? $attribute->admin_name;
        $item->type = $attribute->type;

        return $item;
    }
}
