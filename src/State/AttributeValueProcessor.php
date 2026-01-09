<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Models\Attribute;
use Webkul\BagistoApi\Models\AttributeValue;
use Webkul\BagistoApi\Models\Product;

class AttributeValueProcessor implements ProcessorInterface
{
    /**
     * Process attribute value creation/update.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AttributeValue
    {
        $attributeValue = $data;

        $value = $attributeValue->value;

        $attributeId = null;
        if ($attributeValue->attribute instanceof Attribute) {
            $attributeId = $attributeValue->attribute->id;
            $attributeValue->attribute_id = $attributeId;
            unset($attributeValue->attribute);
        } elseif ($attributeValue->attribute_id) {
            $attributeId = $attributeValue->attribute_id;
        }

        if ($attributeValue->product instanceof Product) {
            $attributeValue->product_id = $attributeValue->product->id;
            unset($attributeValue->product);
        }

        $attributeType = 'text';
        if ($attributeId) {
            $attributeType = DB::table('attributes')
                ->where('id', $attributeId)
                ->value('type') ?? 'text';
        }

        if (is_array($value)) {
            $processedValue = [];
            foreach ($value as $item) {
                if (is_string($item) && str_contains($item, '/api/')) {
                    $processedValue[] = (int) basename($item);
                } elseif (is_numeric($item)) {
                    $processedValue[] = (int) $item;
                } else {
                    $processedValue[] = $item;
                }
            }
            $value = $processedValue;
        } elseif (is_string($value) && str_contains($value, '/api/')) {
            $value = [(int) basename($value)];
        }

        $attributeValue->setValueByType($value, $attributeType);

        if (! $attributeValue->locale) {
            $attributeValue->locale = config('app.locale', 'en');
        }
        if (! $attributeValue->channel) {
            $attributeValue->channel = config('app.default_channel', 'default');
        }

        unset($attributeValue->value);

        $attributeValue->save();

        return $attributeValue;
    }
}
