<?php

namespace Webkul\BagistoApi\GraphQl\Serializer;

use ApiPlatform\GraphQl\Serializer\SerializerContextBuilderInterface;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Operation;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Webkul\BagistoApi\Models\CustomerInvoice;
use Webkul\BagistoApi\Models\CustomerInvoiceAddress;
use Webkul\BagistoApi\Models\CustomerInvoiceItem;
use Webkul\BagistoApi\Models\CustomerOrder;
use Webkul\BagistoApi\Models\CustomerOrderItem;
use Webkul\BagistoApi\Models\CustomerOrderShipment;
use Webkul\BagistoApi\Models\CustomerOrderShipmentItem;

/**
 * Decorates the GraphQL SerializerContextBuilder to fix attribute resolution
 * for multi-word resource names (e.g., CompareItem).
 *
 * API Platform's default implementation uses the name converter to denormalize
 * field names in the selection set (e.g., compareItem → compare_item), but then
 * looks up the wrap field name without denormalization (using lcfirst(shortName)).
 * This mismatch causes empty attributes for multi-word resource names.
 *
 * Also ensures that nested relationships (like CustomerOrder.items) properly
 * include all requested fields in the serialization context.
 */
class FixedSerializerContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly ?NameConverterInterface $nameConverter = null,
    ) {}

    public function create(?string $resourceClass, Operation $operation, array $resolverContext, bool $normalization): array
    {
        $context = $this->decorated->create($resourceClass, $operation, $resolverContext, $normalization);

        // Ensure nested CustomerOrderItem fields are always included
        // This handles the case where nested items don't trigger their own context builder call
        if ($normalization && $resourceClass === CustomerOrder::class) {
            // When processing the parent order, also ensure nested items have proper attributes
            $attributes = $context['attributes'] ?? [];

            // Check if items field is being requested
            if (isset($attributes['items']) && is_array($attributes['items'])) {
                $this->ensureNestedItemsAttributes($attributes['items']);
            }

            // Check if addresses field is being requested
            if (isset($attributes['addresses']) && is_array($attributes['addresses'])) {
                $this->ensureNestedAddressesAttributes($attributes['addresses']);
            }

            // Check if shipments field is being requested
            if (isset($attributes['shipments']) && is_array($attributes['shipments'])) {
                $this->ensureNestedShipmentsAttributes($attributes['shipments']);
            }
        }

        // Ensure nested CustomerInvoiceItem fields are always included
        if ($normalization && $resourceClass === CustomerInvoice::class) {
            $attributes = $context['attributes'] ?? [];

            // Check if items field is being requested
            if (isset($attributes['items']) && is_array($attributes['items'])) {
                $this->ensureNestedInvoiceItemsAttributes($attributes['items']);
            }

            // Check if addresses field is being requested
            if (isset($attributes['addresses']) && is_array($attributes['addresses'])) {
                $this->ensureNestedAddressesAttributes($attributes['addresses']);
            }
        }

        // Handle direct nested CustomerOrderItem serialization
        if ($normalization && $resourceClass === CustomerOrderItem::class) {
            $context = $this->ensureCustomerOrderItemAttributes($context, $resolverContext);
        }

        // Handle direct nested CustomerInvoiceItem serialization
        if ($normalization && $resourceClass === CustomerInvoiceItem::class) {
            $context = $this->ensureCustomerInvoiceItemAttributes($context, $resolverContext);
        }

        // Handle direct nested CustomerInvoiceAddress serialization
        if ($normalization && $resourceClass === CustomerInvoiceAddress::class) {
            $context = $this->ensureAddressAttributes($context, $resolverContext);
        }

        // Handle direct nested CustomerOrderShipment serialization
        if ($normalization && $resourceClass === CustomerOrderShipment::class) {
            $context = $this->ensureShipmentAttributes($context, $resolverContext);
        }

        // Handle direct nested CustomerOrderShipmentItem serialization
        if ($normalization && $resourceClass === CustomerOrderShipmentItem::class) {
            $context = $this->ensureShipmentItemAttributes($context, $resolverContext);
        }

        if (! $normalization || ! ($operation instanceof Mutation)) {
            return $context;
        }

        if (! empty($context['attributes'] ?? null)) {
            return $context;
        }

        $wrapFieldName = lcfirst($operation->getShortName());

        if ($this->nameConverter) {
            $denormalizedName = $this->nameConverter->denormalize($wrapFieldName, $resourceClass);

            if ($denormalizedName !== $wrapFieldName) {
                $context['attributes'] = $this->rebuildMutationAttributes(
                    $resourceClass,
                    $operation,
                    $resolverContext,
                    $denormalizedName
                );
            }
        }

        return $context;
    }

    /**
     * Ensure nested items' attributes include qty fields
     */
    private function ensureNestedItemsAttributes(array $itemsAttributes): void
    {
        // The itemsAttributes should contain edges > node structure for paginated results
        // Recursively ensure qty fields are in node attributes
        if (isset($itemsAttributes['edges']) && is_array($itemsAttributes['edges'])) {
            if (isset($itemsAttributes['edges']['node']) && is_array($itemsAttributes['edges']['node'])) {
                $this->addQtyFieldsToAttributes($itemsAttributes['edges']['node']);
                // Also add database column names in case they're being used
                $this->addQtyFieldsToAttributes($itemsAttributes['edges']);
            }
        }
    }

    /**
     * Add qty fields to an attributes array
     */
    private function addQtyFieldsToAttributes(array &$attributes): void
    {
        $qtyFields = ['qty_ordered', 'qty_shipped', 'qty_invoiced', 'qty_canceled', 'qty_refunded'];

        foreach ($qtyFields as $field) {
            $attributes[$field] = true;
        }
    }

    /**
     * Ensure nested invoice items' attributes include qty field
     */
    private function ensureNestedInvoiceItemsAttributes(array &$itemsAttributes): void
    {
        // The itemsAttributes should contain edges > node structure for paginated results
        if (isset($itemsAttributes['edges']) && is_array($itemsAttributes['edges'])) {
            if (isset($itemsAttributes['edges']['node']) && is_array($itemsAttributes['edges']['node'])) {
                $this->addInvoiceItemFieldsToAttributes($itemsAttributes['edges']['node']);
                $this->addInvoiceItemFieldsToAttributes($itemsAttributes['edges']);
            }
        }
    }

    /**
     * Ensure nested addresses' attributes are properly set
     */
    private function ensureNestedAddressesAttributes(array &$addressAttributes): void
    {
        // The addressAttributes should contain edges > node structure for paginated results
        if (isset($addressAttributes['edges']) && is_array($addressAttributes['edges'])) {
            if (isset($addressAttributes['edges']['node']) && is_array($addressAttributes['edges']['node'])) {
                $this->addAddressFieldsToAttributes($addressAttributes['edges']['node']);
                $this->addAddressFieldsToAttributes($addressAttributes['edges']);
            }
        }
    }

    /**
     * Add invoice item fields to an attributes array
     */
    private function addInvoiceItemFieldsToAttributes(array &$attributes): void
    {
        $itemFields = ['id', 'qty', 'sku', 'name', 'price', 'base_price', 'total', 'base_total', 'tax_amount', 'discount_amount'];

        foreach ($itemFields as $field) {
            $attributes[$field] = true;
        }
    }

    /**
     * Add address fields to an attributes array
     */
    private function addAddressFieldsToAttributes(array &$attributes): void
    {
        // Include both snake_case and camelCase versions
        $addressFields = [
            'id',
            'name',
            'address',
            'city',
            'state',
            'postcode',
            'country_id',
            'countryId',
            'phone',
            'address_type',
            'addressType',
            'first_name',
            'firstName',
            'last_name',
            'lastName',
            'country',
        ];

        foreach ($addressFields as $field) {
            $attributes[$field] = true;
        }
    }

    /**
     * Ensure CustomerOrderItem attributes always include qty fields
     */
    private function ensureCustomerOrderItemAttributes(array $context, array $resolverContext): array
    {
        // Always ensure qty fields are in the attributes for item serialization
        $attributes = $context['attributes'] ?? [];

        // Use snake_case field names to match the denormalization used by the serializer
        $qtyFields = ['id', 'qty_ordered', 'qty_shipped', 'qty_invoiced', 'qty_canceled', 'qty_refunded', 'sku', 'name', 'price', 'base_price', 'total', 'base_total'];

        // Ensure attributes includes all qty fields
        foreach ($qtyFields as $field) {
            if (is_array($attributes)) {
                if (array_key_exists('qty_ordered', $attributes) || ! empty($attributes)) {
                    $attributes[$field] = true;
                } else {
                    $attributes[] = $field;
                }
            } else {
                if (! in_array($field, (array) $attributes)) {
                    $attributes[] = $field;
                }
            }
        }

        $context['attributes'] = $attributes;

        return $context;
    }

    /**
     * Ensure CustomerInvoiceItem attributes include qty field
     */
    private function ensureCustomerInvoiceItemAttributes(array $context, array $resolverContext): array
    {
        $attributes = $context['attributes'] ?? [];

        $fields = ['id', 'qty', 'sku', 'name', 'price', 'base_price', 'total', 'base_total', 'tax_amount', 'discount_amount'];

        foreach ($fields as $field) {
            if (is_array($attributes)) {
                $attributes[$field] = true;
            }
        }

        $context['attributes'] = $attributes;

        return $context;
    }

    /**
     * Ensure address attributes are properly set
     */
    private function ensureAddressAttributes(array $context, array $resolverContext): array
    {
        $attributes = $context['attributes'] ?? [];

        $fields = ['id', 'name', 'address', 'city', 'state', 'postcode', 'country_id', 'phone', 'address_type'];

        foreach ($fields as $field) {
            if (is_array($attributes)) {
                $attributes[$field] = true;
            }
        }

        $context['attributes'] = $attributes;

        return $context;
    }

    /**
     * Ensure nested shipments' attributes include items and addresses
     */
    private function ensureNestedShipmentsAttributes(array &$shipmentsAttributes): void
    {
        // The shipmentsAttributes should contain edges > node structure for paginated results
        if (isset($shipmentsAttributes['edges']) && is_array($shipmentsAttributes['edges'])) {
            if (isset($shipmentsAttributes['edges']['node']) && is_array($shipmentsAttributes['edges']['node'])) {
                $this->addShipmentFieldsToAttributes($shipmentsAttributes['edges']['node']);

                // Also ensure nested fields within the node are properly handled
                if (isset($shipmentsAttributes['edges']['node']['shippingAddress']) && is_array($shipmentsAttributes['edges']['node']['shippingAddress'])) {
                    $this->addAddressFieldsToAttributes($shipmentsAttributes['edges']['node']['shippingAddress']);
                }
                if (isset($shipmentsAttributes['edges']['node']['items']) && is_array($shipmentsAttributes['edges']['node']['items'])) {
                    if (isset($shipmentsAttributes['edges']['node']['items']['edges']) && is_array($shipmentsAttributes['edges']['node']['items']['edges'])) {
                        if (isset($shipmentsAttributes['edges']['node']['items']['edges']['node']) && is_array($shipmentsAttributes['edges']['node']['items']['edges']['node'])) {
                            $this->addShipmentItemFieldsToAttributes($shipmentsAttributes['edges']['node']['items']['edges']['node']);
                        }
                    }
                }

                $this->addShipmentFieldsToAttributes($shipmentsAttributes['edges']);
            }
        }
    }

    /**
     * Add shipment fields to an attributes array
     */
    private function addShipmentFieldsToAttributes(array &$attributes): void
    {
        // Include both snake_case and camelCase versions to handle serializer name conversion
        $shipmentFields = [
            'id',
            'status',
            'total_qty',
            'totalQty',
            'total_weight',
            'totalWeight',
            'carrier_code',
            'carrierCode',
            'carrier_title',
            'carrierTitle',
            'track_number',
            'trackNumber',
            'email_sent',
            'emailSent',
            'shipping_number',
            'shippingNumber',
            'payment_method_title',
            'paymentMethodTitle',
            'shipping_method_title',
            'shippingMethodTitle',
            'created_at',
            'createdAt',
            'items',
            'shipping_address',
            'shippingAddress',
            'billing_address',
            'billingAddress',
        ];

        foreach ($shipmentFields as $field) {
            $attributes[$field] = true;
        }
    }

    /**
     * Add shipment item fields to an attributes array
     */
    private function addShipmentItemFieldsToAttributes(array &$attributes): void
    {
        $itemFields = ['id', 'sku', 'name', 'qty', 'weight', 'description', 'order_item_id'];

        foreach ($itemFields as $field) {
            $attributes[$field] = true;
        }
    }

    /**
     * Ensure CustomerOrderShipment attributes are properly set
     */
    private function ensureShipmentAttributes(array $context, array $resolverContext): array
    {
        $attributes = $context['attributes'] ?? [];

        // Include both snake_case and camelCase versions
        $fields = [
            'id',
            'status',
            'total_qty',
            'totalQty',
            'total_weight',
            'totalWeight',
            'carrier_code',
            'carrierCode',
            'carrier_title',
            'carrierTitle',
            'track_number',
            'trackNumber',
            'email_sent',
            'emailSent',
            'shipping_number',
            'shippingNumber',
            'payment_method_title',
            'paymentMethodTitle',
            'shipping_method_title',
            'shippingMethodTitle',
            'created_at',
            'createdAt',
            'items',
            'shipping_address',
            'shippingAddress',
            'billing_address',
            'billingAddress',
        ];

        foreach ($fields as $field) {
            if (is_array($attributes)) {
                $attributes[$field] = true;
            }
        }

        $context['attributes'] = $attributes;

        return $context;
    }

    /**
     * Ensure CustomerOrderShipmentItem attributes are properly set
     */
    private function ensureShipmentItemAttributes(array $context, array $resolverContext): array
    {
        $attributes = $context['attributes'] ?? [];

        $fields = ['id', 'sku', 'name', 'qty', 'weight', 'description'];

        foreach ($fields as $field) {
            if (is_array($attributes)) {
                $attributes[$field] = true;
            }
        }

        $context['attributes'] = $attributes;

        return $context;
    }

    /**
     * Rebuild mutation attributes using the denormalized wrap field name
     *
     * For Eloquent models, inner field names are denormalized (camelCase → snake_case)
     * since Eloquent properties use snake_case. For non-Eloquent response models
     * (DTOs, action responses), inner field names are kept as-is since PHP properties
     * use camelCase.
     */
    private function rebuildMutationAttributes(
        ?string $resourceClass,
        Operation $operation,
        array $resolverContext,
        string $denormalizedWrapFieldName,
    ): array {
        if (isset($resolverContext['fields'])) {
            $fields = $resolverContext['fields'];
        } elseif (isset($resolverContext['info']) && $resolverContext['info'] instanceof ResolveInfo) {
            $fields = $resolverContext['info']->getFieldSelection(\PHP_INT_MAX);
        } else {
            return [];
        }

        $isEloquent = $resourceClass && is_subclass_of($resourceClass, Model::class);

        /** Denormalize top-level keys to locate the wrap field */
        $topLevel = [];

        foreach ($fields as $key => $value) {
            $denormalizedKey = $this->nameConverter
                ? $this->nameConverter->denormalize((string) $key, $resourceClass)
                : $key;

            $topLevel[$denormalizedKey] = $value;
        }

        $innerFields = $topLevel[$denormalizedWrapFieldName] ?? [];

        if (! \is_array($innerFields)) {
            return [];
        }

        /**
         * For Eloquent models, denormalize inner field names (e.g., productId → product_id).
         * For non-Eloquent models, keep camelCase field names as-is (they match PHP properties).
         */
        return $this->replaceIdKeys($innerFields, $resourceClass, $isEloquent);
    }

    /**
     * Replace _id keys with id and optionally denormalize field names
     */
    private function replaceIdKeys(array $fields, ?string $resourceClass, bool $denormalizeKeys = true): array
    {
        $denormalizedFields = [];

        foreach ($fields as $key => $value) {
            if ($key === '_id') {
                $denormalizedFields['id'] = $fields['_id'];

                continue;
            }

            $denormalizedKey = ($denormalizeKeys && $this->nameConverter)
                ? $this->nameConverter->denormalize((string) $key, $resourceClass)
                : $key;

            $denormalizedFields[$denormalizedKey] = \is_array($value)
                ? $this->replaceIdKeys($value, $resourceClass, $denormalizeKeys)
                : $value;
        }

        return $denormalizedFields;
    }
}
