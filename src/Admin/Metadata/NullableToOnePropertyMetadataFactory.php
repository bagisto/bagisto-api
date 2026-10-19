<?php

namespace Webkul\BagistoApi\Admin\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\TypeInfo\Type as NativeType;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;
use Webkul\BagistoApi\Models\Customer;

class NullableToOnePropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /**
     * The nullable relations.
     */
    private array $nullableRelations = [
        Customer::class => ['status', 'is_verified', 'is_suspended', 'subscribed_to_news_letter'],
        AdminCustomer::class => ['group'],
        AdminCustomerReview::class => ['customer', 'product'],
        AdminMarketingCampaign::class => ['channel', 'customer_group', 'marketing_template'],
        AdminMarketingSearchTerm::class => ['channel'],
        AdminMarketingSubscriber::class => ['channel'],
        AdminInvoice::class => ['order'],
    ];

    /**
     * Columns whose API type must not follow the database driver: tinyint on MySQL reads as a
     * string, boolean on PostgreSQL reads as a boolean, and the contract stays the string.
     *
     * @var array<class-string, string[]>
     */
    private array $stringProperties = [
        Customer::class => ['status', 'is_verified', 'is_suspended'],
    ];

    /** @var array<class-string, string[]> */
    private static array $columnsCache = [];

    /**
     * Create a new instance.
     */
    public function __construct(private readonly PropertyMetadataFactoryInterface $decorated) {}

    /**
     * Create.
     */
    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $metadata = $this->decorated->create($resourceClass, $property, $options);

        if (in_array($property, $this->stringProperties[$resourceClass] ?? [], true)) {
            $metadata = $metadata->withNativeType(NativeType::nullable(NativeType::string()));
        }

        if (! $this->shouldBeNullable($resourceClass, $property)) {
            return $metadata;
        }

        $native = $metadata->getNativeType();

        if (
            $native === null
            || $native->isNullable()
        ) {
            return $metadata;
        }

        return $metadata->withNativeType(NativeType::nullable($native));
    }

    /**
     * Should be nullable.
     */
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
