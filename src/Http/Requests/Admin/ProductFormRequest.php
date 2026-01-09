<?php

namespace Webkul\BagistoApi\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\Core\Rules\Slug;
use Webkul\Product\Helpers\ProductType;

class ProductFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productTypes = implode(',', array_keys(config('product_types', [])));

        return [
            'type'                => 'required|in:'.$productTypes,
            'attributeFamily'     => 'required|exists:attribute_families,id',
            'sku'                 => ['required', 'unique:products,sku', new Slug],
            'name'                => 'required|string',
            'description'         => 'nullable|string',
            'shortDescription'    => 'nullable|string',
            'status'              => 'nullable|boolean',
            'new'                 => 'nullable|boolean',
            'featured'            => 'nullable|boolean',
            'price'               => 'nullable|numeric',
            'special_price'       => 'nullable|numeric',
            'weight'              => 'nullable|numeric',
            'cost'                => 'nullable|numeric',
            'length'              => 'nullable|numeric',
            'width'               => 'nullable|numeric',
            'height'              => 'nullable|numeric',
            'color'               => 'nullable',
            'size'                => 'nullable',
            'brand'               => 'nullable',
            'super_attributes'    => 'array|min:1',
            'super_attributes.*'  => 'array|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'                => trans('api-resources.rest-api.admin.catalog.products.error.type-required'),
            'type.in'                      => trans('api-resources.rest-api.admin.catalog.products.error.type-invalid'),
            'attributeFamily.required'     => trans('api-resources.rest-api.admin.catalog.products.error.attribute-family-required'),
            'attributeFamily.exists'       => trans('api-resources.rest-api.admin.catalog.products.error.attribute-family-exists'),
            'sku.required'                 => trans('api-resources.rest-api.admin.catalog.products.error.sku-required'),
            'sku.unique'                   => trans('api-resources.rest-api.admin.catalog.products.error.sku-unique'),
            'super_attributes.array'       => trans('api-resources.rest-api.admin.catalog.products.error.super-attributes-array'),
            'super_attributes.min'         => trans('api-resources.rest-api.admin.catalog.products.error.super-attributes-min'),
            'super_attributes.*.array'     => trans('api-resources.rest-api.admin.catalog.products.error.super-attributes-array'),
            'super_attributes.*.min'       => trans('api-resources.rest-api.admin.catalog.products.error.super-attributes-min'),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator);
    }

    public function attributes(): array
    {
        return [
            'type'                => trans('api-resources.rest-api.admin.catalog.products.type'),
            'attributeFamily'     => 'attributeFamily',
            'sku'                 => 'sku',
            'name'                => 'name',
            'description'         => 'description',
            'shortDescription'    => 'shortDescription',
            'new'                 => 'new',
            'featured'            => 'featured',
            'price'               => 'price',
            'weight'              => 'weight',
            'cost'                => 'cost',
            'length'              => 'length',
            'width'               => 'width',
            'height'              => 'height',
            'color'               => 'color',
            'size'                => 'size',
            'brand'               => 'brand',
            'super_attributes'    => 'super_attributes',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (
            ProductType::hasVariants($this->input('type'))
            && ! $this->has('super_attributes')
        ) {
            throw new \Illuminate\Validation\ValidationException(
                \Illuminate\Support\Facades\Validator::make(
                    $this->all(),
                    ['super_attributes'          => 'required'],
                    ['super_attributes.required' => trans('api-resources.rest-api.admin.catalog.products.error.configurable-error')]
                )
            );
        }

        return $validated;
    }
}
