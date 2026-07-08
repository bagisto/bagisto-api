<?php

namespace Webkul\BagistoApi\Admin\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Symfony\Component\PropertyInfo\Type;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;

class NullableToOnePropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    private array $nullableRelations = [
        AdminCustomer::class => ['group'],
        AdminCustomerReview::class => ['customer', 'product'],
        // Marketing listings null-out these to-one objects (detail-only) — keep them
        // nullable so a listing row resolves null instead of 500ing the connection node.
        AdminMarketingCampaign::class => ['channel', 'customer_group', 'marketing_template'],
        AdminMarketingSearchTerm::class => ['channel'],
        AdminMarketingSubscriber::class => ['channel'],
        // Invoice listing rows expose the linked order only on the detail query;
        // on the listing the nested `order` resolves null (flat orderIncrementId
        // etc. cover the listing). Keep it nullable so the listing node doesn't 500.
        AdminInvoice::class => ['order'],
    ];

    public function __construct(private readonly PropertyMetadataFactoryInterface $decorated) {}

    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
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
