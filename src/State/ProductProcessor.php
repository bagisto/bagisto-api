<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Models\Attribute;
use Webkul\BagistoApi\Models\AttributeFamily;
use Webkul\BagistoApi\Models\AttributeValue;
use Webkul\BagistoApi\Models\Category;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Validators\ProductValidator;

class ProductProcessor implements ProcessorInterface
{
    public $configurableAttributes;

    public $familyAttributes;

    public $systemAttributes = [
        'sku', 'name', 'url_key', 'tax_category_id', 'new', 'featured', 'visible_individually', 'status', 'short_description', 'description', 'price', 'special_price', 'special_price_from', 'special_price_to', 'meta_title', 'meta_keywords', 'weight', 'guest_checkout', 'product_number', 'manage_stock',
        // User-defined attributes from seeder
        'cost', 'meta_description', 'length', 'width', 'height', 'color', 'size', 'brand',
    ];

    /**
     * Create a new processor instance.
     */
    public function __construct(
        protected ProductValidator $validator
    ) {}

    /**
     * Process product creation or update.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
        $product = $data;

        if ($operation->getName() === 'create') {
            $this->validator->validateForCreation($product);
        } elseif ($operation->getName() === 'update') {
            $this->validator->validateForUpdate($product);
        }

        $systemAttributeValues = $this->extractSystemAttributes($product);

        if ($product->parent instanceof Product) {
            $product->parent_id = $product->parent->id;

            unset($product->parent);
        }

        if ($product->attribute_family instanceof AttributeFamily) {
            $this->familyAttributes = $product->attribute_family->getCustomAttributesAttribute();

            $this->configurableAttributes = $product->attribute_family->getConfigurableAttributesAttribute();

            $product->attribute_family_id = $product->attribute_family->id;

            unset($product->attribute_family);
        }

        $superAttributesCollection = null;
        if (isset($product->super_attributes)) {
            $superAttributesCollection = $product->super_attributes;

            unset($product->super_attributes);
        }

        $categoryCollection = null;

        if (isset($product->categories)) {
            $categoryCollection = $product->categories;

            unset($product->categories);
        }

        $productChannels = null;

        if (isset($product->channels)) {
            $productChannels = $product->channels;

            unset($product->channels);
        }

        $product->save();

        if (! empty($superAttributesCollection)) {
            $attributeIds = [];

            foreach ($superAttributesCollection as $item) {
                if ($item instanceof Attribute) {
                    $attributeIds[] = $item->id;
                } elseif (is_numeric($item)) {
                    $attributeIds[] = (int) $item;
                }
            }

            if (! empty($attributeIds)) {
                $product->super_attributes()->sync($attributeIds);
            }
        }

        if (! empty($categoryCollection)) {
            $categoryIds = [];

            foreach ($categoryCollection as $item) {
                if ($item instanceof Category) {
                    $categoryIds[] = $item->id;
                } elseif (is_numeric($item)) {
                    $categoryIds[] = (int) $item;
                }
            }

            if (! empty($categoryIds)) {
                $product->categories()->sync($categoryIds);
            }
        }

        if (! empty($productChannels)) {
            $channelIds = [];

            foreach ($productChannels as $item) {
                if ($item instanceof \Webkul\Core\Models\Channel) {
                    $channelIds[] = $item->id;
                } elseif (is_numeric($item)) {
                    $channelIds[] = (int) $item;
                }
            }

            if (! empty($channelIds)) {
                $product->channels()->sync($channelIds);
            }
        }

        $this->processSystemAttributes($product, $systemAttributeValues);

        $product->load(['attribute_family']);

        return $product;
    }

    /**
     * Extract system attribute values from temporary storage.
     */
    protected function extractSystemAttributes(Product $product): array
    {
        $systemAttributeValues = [];

        $attributes = $product->getAttributes();

        foreach ($this->systemAttributes as $attributeCode) {
            $tempKey = "_temp_{$attributeCode}";

            if (isset($attributes[$tempKey])) {
                $systemAttributeValues[$attributeCode] = $attributes[$tempKey];
                unset($product->{$tempKey});

            }

            if (isset($attributes[$attributeCode])) {
                $systemAttributeValues[$attributeCode] = $attributes[$attributeCode];
                if ($attributeCode !== 'sku') {
                    unset($product->{$attributeCode});
                }
            }
        }

        return $systemAttributeValues;
    }

    /**
     * Process system attributes and sync to product_attribute_values table.
     */
    protected function processSystemAttributes(Product $product, array $systemAttributeValues): void
    {
        if (empty($systemAttributeValues)) {
            return;
        }

        foreach ($systemAttributeValues as $attributeCode => $value) {
            if (! in_array($attributeCode, $this->systemAttributes)) {
                continue;
            }

            $attribute = $this->familyAttributes->firstWhere('code', $attributeCode);
            if (! $attribute) {
                continue;
            }

            $channel = $attribute->value_per_channel ? $product->getChannel() ?? core()->getDefaultChannelCode() : null;

            $locale = $attribute->value_per_locale ? $product->getLocale() ?? core()->getDefaultLocaleCodeFromDefaultChannel() : null;

            $attributeValue = $product->attribute_values()
                ->where('attribute_id', $attribute->id)
                ->where('locale', $locale)
                ->where('channel', $channel)
                ->first();

            if (! $attributeValue) {
                $attributeValue = new AttributeValue;
                $attributeValue->product_id = $product->id;
                $attributeValue->attribute_id = $attribute->id;
                $attributeValue->locale = $locale;
                $attributeValue->channel = $channel;
                $attributeValue->unique_id = implode('|', array_filter([
                    $product->id,
                    $attribute->id,
                    $locale,
                    $channel,
                ]));

            }

            $attributeValue->setValueByType($value, $attribute->type);

            $this->setAttributeTypeColumnValues($attribute, $value, $attributeValue);

            $attributeValue->save();
        }
    }

    /**
     * Set attribute type column values.
     */
    public function setAttributeTypeColumnValues($attribute, $value, &$attributeValue)
    {
        $attributeValue->{$attribute->column_name} = $value;
    }
}
