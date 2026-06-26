<?php

namespace Webkul\BagistoApi\Admin\Metadata;

use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Symfony\Component\PropertyInfo\Type;

class NullableToOnePropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    private array $nullableRelations = [
        \Webkul\BagistoApi\Admin\Models\AdminCustomer::class => ['group'],
        \Webkul\BagistoApi\Admin\Models\AdminCustomerReview::class => ['customer', 'product'],
        // Marketing listings null-out these to-one objects (detail-only) — keep them
        // nullable so a listing row resolves null instead of 500ing the connection node.
        \Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign::class => ['channel', 'customer_group', 'marketing_template'],
        \Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm::class => ['channel'],
        \Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber::class => ['channel'],
        // Invoice listing rows expose the linked order only on the detail query;
        // on the listing the nested `order` resolves null (flat orderIncrementId
        // etc. cover the listing). Keep it nullable so the listing node doesn't 500.
        \Webkul\BagistoApi\Admin\Models\AdminInvoice::class => ['order'],
    ];

    public function __construct(private readonly PropertyMetadataFactoryInterface $decorated) {}

    public function create(string $resourceClass, string $property, array $options = []): \ApiPlatform\Metadata\ApiProperty
    {
        $metadata = $this->decorated->create($resourceClass, $property, $options);

        if (! in_array($property, $this->nullableRelations[$resourceClass] ?? [], true)) {
            return $metadata;
        }

        $types = $metadata->getBuiltinTypes() ?? [];

        if ($types === []) {
            return $metadata;
        }

        $nullable = [];
        foreach ($types as $type) {
            $nullable[] = new Type(
                $type->getBuiltinType(),
                true,
                $type->getClassName(),
                $type->isCollection(),
                $type->getCollectionKeyTypes(),
                $type->getCollectionValueTypes(),
            );
        }

        return $metadata->withBuiltinTypes($nullable);
    }
}
