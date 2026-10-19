<?php

namespace Webkul\BagistoApi\GraphQl\Serializer;

use ApiPlatform\GraphQl\Serializer\SerializerContextBuilderInterface;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Operation;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Models\CustomerInvoice;
use Webkul\BagistoApi\Models\CustomerInvoiceAddress;
use Webkul\BagistoApi\Models\CustomerInvoiceItem;
use Webkul\BagistoApi\Models\CustomerOrder;
use Webkul\BagistoApi\Models\CustomerOrderItem;
use Webkul\BagistoApi\Models\CustomerOrderShipment;
use Webkul\BagistoApi\Models\CustomerOrderShipmentItem;

class FixedSerializerContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly ?NameConverterInterface $nameConverter = null,
    ) {}

    /**
     * Create.
     */
    public function create(?string $resourceClass, Operation $operation, array $resolverContext, bool $normalization): array
    {
        $context = $this->decorated->create($resourceClass, $operation, $resolverContext, $normalization);

        if (
            $normalization
            && $resourceClass === CustomerOrder::class
        ) {
            $attributes = $context['attributes'] ?? [];

            if (
                isset($attributes['items'])
                && is_array($attributes['items'])
            ) {
                $this->ensureNestedItemsAttributes($attributes['items']);
            }

            if (
                isset($attributes['addresses'])
                && is_array($attributes['addresses'])
            ) {
                $this->ensureNestedAddressesAttributes($attributes['addresses']);
            }

            if (
                isset($attributes['shipments'])
                && is_array($attributes['shipments'])
            ) {
                $this->ensureNestedShipmentsAttributes($attributes['shipments']);
            }
        }

        if (
            $normalization
            && $resourceClass === CustomerInvoice::class
        ) {
            $attributes = $context['attributes'] ?? [];

            if (
                isset($attributes['items'])
                && is_array($attributes['items'])
            ) {
                $this->ensureNestedInvoiceItemsAttributes($attributes['items']);
            }

            if (
                isset($attributes['addresses'])
                && is_array($attributes['addresses'])
            ) {
                $this->ensureNestedAddressesAttributes($attributes['addresses']);
            }
        }

        if (
            $normalization
            && $resourceClass === CustomerOrderItem::class
        ) {
            $context = $this->ensureCustomerOrderItemAttributes($context, $resolverContext);
        }

        if (
            $normalization
            && $resourceClass === CustomerInvoiceItem::class
        ) {
            $context = $this->ensureCustomerInvoiceItemAttributes($context, $resolverContext);
        }

        if (
            $normalization
            && $resourceClass === CustomerInvoiceAddress::class
        ) {
            $context = $this->ensureAddressAttributes($context, $resolverContext);
        }

        if (
            $normalization
            && $resourceClass === CustomerOrderShipment::class
        ) {
            $context = $this->ensureShipmentAttributes($context, $resolverContext);
        }

        if (
            $normalization
            && $resourceClass === CustomerOrderShipmentItem::class
        ) {
            $context = $this->ensureShipmentItemAttributes($context, $resolverContext);
        }

        if (
            ! $normalization
            || ! ($operation instanceof Mutation)
        ) {
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
     * Ensure nested items' attributes include qty fields.
     */
    private function ensureNestedItemsAttributes(array $itemsAttributes): void
    {
        if (
            isset($itemsAttributes['edges'])
            && is_array($itemsAttributes['edges'])
        ) {
            if (
                isset($itemsAttributes['edges']['node'])
                && is_array($itemsAttributes['edges']['node'])
            ) {
                $this->addQtyFieldsToAttributes($itemsAttributes['edges']['node']);
                $this->addQtyFieldsToAttributes($itemsAttributes['edges']);
            }
        }
    }

    /**
     * Add qty fields to an attributes array.
     */
    private function addQtyFieldsToAttributes(array &$attributes): void
    {
        $qtyFields = ['qty_ordered', 'qty_shipped', 'qty_invoiced', 'qty_canceled', 'qty_refunded'];

        foreach ($qtyFields as $field) {
            $attributes[$field] = true;
        }
    }

    /**
     * Ensure nested invoice items' attributes include qty field.
     */
    private function ensureNestedInvoiceItemsAttributes(array &$itemsAttributes): void
    {
        if (
            isset($itemsAttributes['edges'])
            && is_array($itemsAttributes['edges'])
        ) {
            if (
                isset($itemsAttributes['edges']['node'])
                && is_array($itemsAttributes['edges']['node'])
            ) {
                $this->addInvoiceItemFieldsToAttributes($itemsAttributes['edges']['node']);
                $this->addInvoiceItemFieldsToAttributes($itemsAttributes['edges']);
            }
        }
    }

    /**
     * Ensure nested addresses' attributes are properly set.
     */
    private function ensureNestedAddressesAttributes(array &$addressAttributes): void
    {
        if (
            isset($addressAttributes['edges'])
            && is_array($addressAttributes['edges'])
        ) {
            if (
                isset($addressAttributes['edges']['node'])
                && is_array($addressAttributes['edges']['node'])
            ) {
                $this->addAddressFieldsToAttributes($addressAttributes['edges']['node']);
                $this->addAddressFieldsToAttributes($addressAttributes['edges']);
            }
        }
    }

    /**
     * Add invoice item fields to an attributes array.
     */
    private function addInvoiceItemFieldsToAttributes(array &$attributes): void
    {
        $itemFields = ['id', 'qty', 'sku', 'name', 'price', 'base_price', 'total', 'base_total', 'tax_amount', 'discount_amount'];

        foreach ($itemFields as $field) {
            $attributes[$field] = true;
        }
    }

    /**
     * Add address fields to an attributes array.
     */
    private function addAddressFieldsToAttributes(array &$attributes): void
    {
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
     * Ensure CustomerOrderItem attributes always include qty fields.
     */
    private function ensureCustomerOrderItemAttributes(array $context, array $resolverContext): array
    {
        $attributes = $context['attributes'] ?? [];

        $qtyFields = ['id', 'qty_ordered', 'qty_shipped', 'qty_invoiced', 'qty_canceled', 'qty_refunded', 'sku', 'name', 'price', 'base_price', 'total', 'base_total'];

        foreach ($qtyFields as $field) {
            if (is_array($attributes)) {
                if (
                    array_key_exists('qty_ordered', $attributes)
                    || ! empty($attributes)
                ) {
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
     * Ensure CustomerInvoiceItem attributes include qty field.
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
     * Ensure address attributes are properly set.
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
     * Ensure nested shipments' attributes include items and addresses.
     */
    private function ensureNestedShipmentsAttributes(array &$shipmentsAttributes): void
    {
        if (! is_array($shipmentsAttributes['edges']['node'] ?? null)) {
            return;
        }

        $node = &$shipmentsAttributes['edges']['node'];

        $this->addShipmentFieldsToAttributes($node);

        if (is_array($node['shippingAddress'] ?? null)) {
            $this->addAddressFieldsToAttributes($node['shippingAddress']);
        }

        if (is_array($node['items']['edges']['node'] ?? null)) {
            $this->addShipmentItemFieldsToAttributes($node['items']['edges']['node']);
        }

        unset($node);

        $this->addShipmentFieldsToAttributes($shipmentsAttributes['edges']);
    }

    /**
     * Add shipment fields to an attributes array.
     */
    private function addShipmentFieldsToAttributes(array &$attributes): void
    {
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
     * Add shipment item fields to an attributes array.
     */
    private function addShipmentItemFieldsToAttributes(array &$attributes): void
    {
        $itemFields = ['id', 'sku', 'name', 'qty', 'weight', 'description', 'order_item_id'];

        foreach ($itemFields as $field) {
            $attributes[$field] = true;
        }
    }

    /**
     * Ensure CustomerOrderShipment attributes are properly set.
     */
    private function ensureShipmentAttributes(array $context, array $resolverContext): array
    {
        $attributes = $context['attributes'] ?? [];

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
     * Ensure CustomerOrderShipmentItem attributes are properly set.
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
     * For Eloquent models, inner field names are denormalized (camelCase → snake_case).
     */
    private function rebuildMutationAttributes(
        ?string $resourceClass,
        Operation $operation,
        array $resolverContext,
        string $denormalizedWrapFieldName,
    ): array {
        if (isset($resolverContext['fields'])) {
            $fields = $resolverContext['fields'];
        } elseif (
            isset($resolverContext['info'])
            && $resolverContext['info'] instanceof ResolveInfo
        ) {
            $fields = $resolverContext['info']->getFieldSelection(\PHP_INT_MAX);
        } else {
            return [];
        }

        $isEloquent = $resourceClass && (
            is_subclass_of($resourceClass, Model::class)
            || str_starts_with($resourceClass, 'Webkul\\BagistoApi\\Admin\\')
            || is_a($resourceClass, SnakeCaseFieldsResource::class, true)
        );

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

        return $this->replaceIdKeys($innerFields, $resourceClass, $isEloquent);
    }

    /**
     * Replace _id keys with id and optionally denormalize field names.
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
