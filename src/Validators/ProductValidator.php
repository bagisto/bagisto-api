<?php

namespace Webkul\BagistoApi\Validators;

use GraphQL\Error\UserError;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Models\Attribute;
use Webkul\BagistoApi\Models\AttributeFamily;
use Webkul\BagistoApi\Models\Product;
use Webkul\Core\Rules\Slug;
use Webkul\Product\Helpers\ProductType;

class ProductValidator
{
    /**
     * Validate product data for creation.
     *
     * @throws UserError
     */
    public function validateForCreation(Product $product): void
    {
        $data = $this->extractValidationData($product);

        $this->validateCreateFields($data);
    }

    /**
     * Validate product data for update.
     *
     * @throws UserError
     */
    public function validateForUpdate(Product $product): void
    {
        $data = $this->extractValidationData($product);

        $this->validateUpdateFields($data, $product->id);
    }

    /**
     * Extract validation data from product model.
     */
    protected function extractValidationData(Product $product): array
    {
        $data = [];

        foreach ($product->getAttributes() as $key => $value) {
            $data[str_starts_with($key, '_temp_') ? str_replace('_temp_', '', $key) : $key] = $value;
        }

        if ($product->attribute_family instanceof AttributeFamily) {
            $data['attributeFamily'] = $product->attribute_family->id;
        }

        if (isset($product->super_attributes)) {
            $data['super_attributes'] = $this->normalizeSuperAttributes($product->super_attributes);
        }

        return $data;
    }

    /**
     * Validate required fields for product creation.
     *
     * @throws UserError
     */
    protected function validateRequiredFields(array $data): void
    {
        $productTypes = implode(',', array_keys(config('product_types', [])));

        $rules = [
            'type' => 'required|in:'.$productTypes,
            'attributeFamily' => 'required|exists:attribute_families,id',
            'sku' => ['required', 'unique:products,sku', new Slug],
            'name' => 'required|string',
            'description' => 'nullable|string',
            'shortDescription' => 'nullable|string',
            'status' => 'nullable|boolean',
            'new' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'price' => 'nullable|numeric',
            'special_price' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'color' => 'nullable',
            'size' => 'nullable',
            'brand' => 'nullable',
        ];

        if (ProductType::hasVariants($data['type'] ?? '')) {
            $rules['super_attributes'] = 'required|array|min:1';
        }

        $messages = [
            'type.required' => 'Product type is required',
            'type.in' => 'Invalid product type',
            'attributeFamily.required' => 'Attribute family is required',
            'attributeFamily.exists' => 'Attribute family does not exist',
            'sku.required' => 'SKU is required',
            'sku.unique' => 'SKU already exists',
            'name.required' => 'Product name is required',
            'super_attributes.required' => 'Configurable products must have super attributes',
            'super_attributes.array' => 'Super attributes must be an array',
            'super_attributes.min' => 'At least one super attribute is required',
        ];

        $this->runValidation($data, $rules, $messages);
    }

    /**
     * Validate required fields for product creation.
     *
     * @throws UserError
     */
    protected function validateCreateFields(array $data): void
    {
        $productTypes = implode(',', array_keys(config('product_types', [])));

        $rules = [
            'type' => 'required|in:'.$productTypes,
            'attributeFamily' => 'required|exists:attribute_families,id',
            'sku' => ['required', 'unique:products,sku', new Slug],
        ];

        if (ProductType::hasVariants($data['type'] ?? '')) {
            $rules['super_attributes'] = 'required|array|min:1';
        }

        $messages = [
            'type.required' => 'Product type is required',
            'type.in' => 'Invalid product type',
            'attributeFamily.required' => 'Attribute family is required',
            'attributeFamily.exists' => 'Attribute family does not exist',
            'sku.required' => 'SKU is required',
            'sku.unique' => 'SKU already exists',
            'super_attributes.required' => 'Configurable products must have super attributes',
            'super_attributes.array' => 'Super attributes must be an array',
            'super_attributes.min' => 'At least one super attribute is required',
        ];

        $this->runValidation($data, $rules, $messages);
    }

    /**
     * Validate fields for product update.
     *
     * @throws UserError
     */
    protected function validateUpdateFields(array $data, int $productId): void
    {
        $rules = [];

        if (isset($data['sku'])) {
            $rules['sku'] = ['required', 'unique:products,sku,'.$productId, new Slug];
        }

        if (isset($data['name'])) {
            $rules['name'] = 'required|string';
        }

        if (isset($data['price'])) {
            $rules['price'] = 'nullable|numeric';
        }

        if (isset($data['weight'])) {
            $rules['weight'] = 'nullable|numeric';
        }

        if (! empty($rules)) {
            $this->runValidation($data, $rules);
        }
    }

    /**
     * Run validation and throw UserError if fails.
     *
     * @throws UserError
     */
    protected function runValidation(array $data, array $rules, array $messages = []): void
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $errorMessages = [];

            foreach ($errors as $field => $fieldMessages) {
                foreach ($fieldMessages as $message) {
                    $errorMessages[] = "{$field}: {$message}";
                }
            }

            throw new UserError(implode(', ', $errorMessages));
        }
    }

    /**
     * Reduce super attributes to a list of IDs, codes or arrays.
     */
    private function normalizeSuperAttributes(mixed $superAttributes): mixed
    {
        if (! is_iterable($superAttributes)) {
            return $superAttributes;
        }

        $normalized = [];

        foreach ($superAttributes as $attribute) {
            $normalized[] = $attribute instanceof Attribute ? $attribute->id : $attribute;
        }

        return $normalized;
    }
}
