<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminAttributeOptionSwatchProcessor;

/**
 * Image swatch upload for one attribute option. REST only — a binary upload is not
 * transportable over JSON GraphQL.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAttributeOptionSwatch',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/attributes/{attributeId}/options/{optionId}/swatch',
            requirements: ['attributeId' => '\d+', 'optionId' => '\d+'],
            inputFormats: ['multipart' => ['multipart/form-data']],
            deserialize: false,
            read: false,
            validate: false,
            status: 200,
            processor: AdminAttributeOptionSwatchProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'Upload an option swatch image',
                description: 'Stores the image as the option\'s swatch value. Only for attributes whose swatch type is `image`. Send multipart/form-data with `swatch_value` (bmp, jpeg, jpg, png, webp). Optional `swatch_file_name` names the stored file and `swatch_alt` is saved for the `locale` sent (or the default locale). Returns the full attribute detail.',
                parameters: [
                    new Model\Parameter('attributeId', 'path', 'Attribute ID.', true, schema: ['type' => 'integer', 'example' => 23]),
                    new Model\Parameter('optionId', 'path', 'Option ID.', true, schema: ['type' => 'integer', 'example' => 45]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['swatch_value'],
                                'properties' => [
                                    'swatch_value' => ['type' => 'string', 'format' => 'binary'],
                                    'swatch_file_name' => ['type' => 'string', 'example' => 'navy-blue'],
                                    'swatch_alt' => ['type' => 'string', 'example' => 'Navy blue fabric'],
                                    'locale' => ['type' => 'string', 'example' => 'en'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Swatch stored. Returns the full attribute detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 23,
                                    'code' => 'color',
                                    'type' => 'select',
                                    'swatchType' => 'image',
                                    'options' => [
                                        [
                                            'id' => 45,
                                            'adminName' => 'Navy',
                                            'sortOrder' => 1,
                                            'swatchValue' => 'attribute_option/navy-blue.png',
                                            'swatchValueUrl' => 'https://example.com/cache/small/attribute_option/navy-blue.png',
                                            'swatchAlt' => 'Navy blue fabric',
                                            'swatchFileName' => 'navy-blue',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing catalog.attributes.edit permission.'),
                    '404' => new Model\Response(description: 'Attribute or option not found.'),
                    '422' => new Model\Response(description: 'Missing or invalid file, or the attribute does not use image swatches.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class AdminAttributeOptionSwatch
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $optionId = null;

    #[ApiProperty(writable: false)]
    public ?int $attributeId = null;
}
