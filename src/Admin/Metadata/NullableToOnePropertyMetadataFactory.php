<?php

namespace Webkul\BagistoApi\Admin\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type as NativeType;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;

class NullableToOnePropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /** To-one relations that legitimately resolve null (listing rows, missing owner). */
    private array $nullableRelations = [
        AdminCustomer::class => ['group'],
        AdminCustomerReview::class => ['customer', 'product'],
        AdminMarketingCampaign::class => ['channel', 'customer_group', 'marketing_template'],
        AdminMarketingSearchTerm::class => ['channel'],
        AdminMarketingSubscriber::class => ['channel'],
        AdminInvoice::class => ['order'],
    ];

    /** @var array<class-string, string[]> */
    private static array $columnsCache = [];

    public function __construct(private readonly PropertyMetadataFactoryInterface $decorated) {}

    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $metadata = $this->decorated->create($resourceClass, $property, $options);

        if (! $this->shouldBeNullable($resourceClass, $property)) {
            return $metadata;
        }

        $native = $metadata->getNativeType();

        if ($native !== null) {
            return $native->isNullable()
                ? $metadata
                : $metadata->withNativeType(NativeType::nullable($native));
        }

        $types = $metadata->getBuiltinTypes() ?? [];

        if ($types === []) {
            return $metadata;
        }

        $nullable = [];

        foreach ($types as $type) {
            $nullable[] = new LegacyType(
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

    /** api-platform 4.3 types Eloquent properties from the DB schema, so anything that is not a real column is wrongly non-null. */
    private function shouldBeNullable(string $resourceClass, string $property): bool
    {
        if (in_array($property, $this->nullableRelations[$resourceClass] ?? [], true)) {
            return true;
        }

        if (! is_subclass_of($resourceClass, Model::class)) {
            return false;
        }

        return ! in_array($property, $this->columnsOf($resourceClass), true);
    }

    /**
     * @return string[]
     */
    private function columnsOf(string $resourceClass): array
    {
        if (array_key_exists($resourceClass, self::$columnsCache)) {
            return self::$columnsCache[$resourceClass];
        }

        try {
            $model = new $resourceClass;

            $columns = Schema::connection($model->getConnectionName())
                ->getColumnListing($model->getTable());
        } catch (\Throwable) {
            $columns = [];
        }

        return self::$columnsCache[$resourceClass] = $columns;
    }
}
