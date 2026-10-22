<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\State\ReturnCustomFieldProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ReturnCustomField',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/return-custom-fields',
            provider: ReturnCustomFieldProvider::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'List the active custom fields of the return form',
                description: 'The additional fields the storefront return form renders. Answer them with `customAttributes` when creating a return, keyed by the field `id`. Fields with `isRequired: true` must be answered; `select`/`radio` answers must be one of the option `value`s, `multiselect`/`checkbox` answers a list of them.',
                responses: [
                    '200' => new Response(
                        description: 'Active custom fields, in the order the storefront renders them.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'code' => 'invoice_number',
                                        'label' => 'Invoice number',
                                        'type' => 'text',
                                        'isRequired' => true,
                                        'position' => 1,
                                        'inputValidation' => 'numeric',
                                        'options' => [],
                                    ], [
                                        'id' => 2,
                                        'code' => 'preferred_pickup_slot',
                                        'label' => 'Preferred pickup slot',
                                        'type' => 'select',
                                        'isRequired' => false,
                                        'position' => 2,
                                        'inputValidation' => null,

                                        'options' => [
                                            [
                                                'id' => 3,
                                                'name' => 'Morning',
                                                'value' => 'morning',
                                            ], [
                                                'id' => 4,
                                                'name' => 'Evening',
                                                'value' => 'evening',
                                            ],
                                        ],
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
            provider: ReturnCustomFieldProvider::class,
            description: 'The active custom fields the return form must collect, with their options and validation, as a plain list (not paginated). Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            paginationType: 'cursor',
        ),
    ],
)]
class ReturnCustomField
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The code.
     */
    public ?string $code = null;

    /**
     * The label.
     */
    public ?string $label = null;

    /**
     * The type.
     */
    public ?string $type = null;

    /**
     * The is required.
     */
    public ?bool $is_required = null;

    /**
     * The position.
     */
    public ?int $position = null;

    /**
     * The input validation.
     */
    public ?string $input_validation = null;

    /**
     * The selectable options of the custom field.
     *
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $options = null;
}
