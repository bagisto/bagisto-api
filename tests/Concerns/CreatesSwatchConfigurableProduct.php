<?php

namespace Webkul\BagistoApi\Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;

/**
 * A storefront-visible configurable product whose one super attribute uses a given swatch type.
 */
trait CreatesSwatchConfigurableProduct
{
    /**
     * Two variants: one saleable carrying `used`, one disabled carrying `unused`, which the
     * storefront therefore does not offer.
     *
     * @return array{productId: int, attributeId: int, variantId: int, used: int, unused: int}
     */
    protected function createSwatchConfigurableProduct(?string $swatchType, string $swatchValue): array
    {
        $attribute = Attribute::query()->create([
            'code' => 'swatch_'.uniqid(),
            'admin_name' => 'Swatch',
            'type' => 'select',
            'swatch_type' => $swatchType,
            'is_configurable' => 1,
            'is_user_defined' => 1,
        ]);

        $used = (int) AttributeOption::query()->create([
            'attribute_id' => $attribute->id,
            'admin_name' => 'Used',
            'sort_order' => 1,
            'swatch_value' => $swatchValue,
        ])->id;

        $unused = (int) AttributeOption::query()->create([
            'attribute_id' => $attribute->id,
            'admin_name' => 'Unused',
            'sort_order' => 2,
            'swatch_value' => $swatchValue,
        ])->id;

        $parent = $this->createBaseProduct('configurable', ['sku' => 'SWATCH-PARENT-'.uniqid()]);

        DB::table('attribute_group_mappings')->insert([
            'attribute_id' => $attribute->id,
            'attribute_group_id' => DB::table('attribute_groups')->where('attribute_family_id', $parent->attribute_family_id)->value('id'),
            'position' => 99,
        ]);

        $child = $this->createBaseProduct('simple', [
            'sku' => 'SWATCH-CHILD-'.uniqid(),
            'parent_id' => $parent->id,
        ]);

        DB::table('product_super_attributes')->insert([
            'product_id' => $parent->id,
            'attribute_id' => $attribute->id,
        ]);

        $this->ensureInventory($child, 50);
        $this->upsertProductAttributeValue($child->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($child->id, $attribute->code, $used, null, 'default');

        $disabled = $this->createBaseProduct('simple', [
            'sku' => 'SWATCH-DISABLED-'.uniqid(),
            'parent_id' => $parent->id,
        ]);

        $this->upsertProductAttributeValue($disabled->id, $attribute->code, $unused, null, 'default');
        $this->upsertProductAttributeValue($disabled->id, 'status', 0, null, 'default');

        return [
            'productId' => (int) $parent->id,
            'attributeId' => (int) $attribute->id,
            'variantId' => (int) $child->id,
            'used' => $used,
            'unused' => $unused,
        ];
    }
}
