<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\State\CompareAttributeProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CompareAttribute',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/compare-attributes',
            provider: CompareAttributeProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['CompareItem'],
                summary: 'List the comparable attributes',
                description: 'Returns the attributes marked comparable that belong to an attribute family, excluding name and price, in the requested locale. These are the rows of the storefront compare page; read each value from the matching product field.',
                responses: [
                    '200' => new Response(
                        description: 'The comparable attributes.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 23,
                                        'code' => 'color',
                                        'adminName' => 'Color',
                                        'name' => 'Color',
                                        'type' => 'select',
                                    ],
                                    [
                                        'id' => 24,
                                        'code' => 'size',
                                        'adminName' => 'Size',
                                        'name' => 'Size',
                                        'type' => 'select',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CompareAttributeProvider::class,
            paginationEnabled: false,
            description: 'List the attributes the storefront compare page renders as comparison rows.',
        ),
    ],
)]
class CompareAttribute
{
    /**
     * The attribute id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The attribute code, also the product field that holds the value.
     */
    #[ApiProperty(writable: false)]
    public ?string $code = null;

    /**
     * The admin name of the attribute.
     */
    #[ApiProperty(writable: false)]
    public ?string $admin_name = null;

    /**
     * The attribute label in the requested locale.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The attribute type.
     */
    #[ApiProperty(writable: false)]
    public ?string $type = null;
}
