<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaCustomFieldCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaCustomFieldUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaCustomField',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/custom-fields',
            provider: AdminRmaCustomFieldCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List RMA custom fields',
                description: 'Extra fields the return form collects. Filters: code (LIKE), label (LIKE), type, status. Sort: id (default desc), position, code.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA custom fields. options is populated only for select/multiselect/checkbox/radio types.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 4,
                                            'code' => 'preferred_resolution',
                                            'label' => 'Preferred resolution',
                                            'type' => 'select',
                                            'isRequired' => 1,
                                            'position' => 1,
                                            'inputValidation' => null,
                                            'status' => 1,
                                            'options' => [
                                                ['id' => 11, 'name' => 'Refund', 'value' => 'refund'],
                                                ['id' => 12, 'name' => 'Replacement', 'value' => 'replacement'],
                                            ],
                                            'message' => null,
                                            'createdAt' => '2026-07-20T09:00:00+00:00',
                                            'updatedAt' => '2026-07-20T09:00:00+00:00',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 1,
                                        'from' => 1,
                                        'to' => 1,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/rma/custom-fields/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaCustomFieldItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Get an RMA custom field (with its options)',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA custom field with its options.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 4,
                                    'code' => 'preferred_resolution',
                                    'label' => 'Preferred resolution',
                                    'type' => 'select',
                                    'isRequired' => 1,
                                    'position' => 1,
                                    'inputValidation' => null,
                                    'status' => 1,
                                    'options' => [
                                        ['id' => 11, 'name' => 'Refund', 'value' => 'refund'],
                                        ['id' => 12, 'name' => 'Replacement', 'value' => 'replacement'],
                                    ],
                                    'message' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-20T09:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/rma/custom-fields',
            input: AdminRmaCustomFieldCreateInput::class,
            processor: AdminRmaCustomFieldProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Create an RMA custom field',
                description: '`options` is required for select/multiselect/checkbox/radio types. Permission: sales.rma.custom-fields.create.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'label', 'type'],
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'preferred_resolution'],
                                    'label' => ['type' => 'string', 'example' => 'Preferred resolution'],
                                    'type' => ['type' => 'string', 'enum' => ['text', 'textarea', 'select', 'multiselect', 'checkbox', 'radio'], 'example' => 'select'],
                                    'is_required' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'position' => ['type' => 'integer', 'example' => 1],
                                    'input_validation' => ['type' => 'string', 'example' => null],
                                    'status' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'options' => [
                                        'type' => 'array',
                                        'description' => 'Required for select/multiselect/checkbox/radio.',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'name' => ['type' => 'string'],
                                                'value' => ['type' => 'string'],
                                            ],
                                        ],
                                        'example' => [
                                            ['name' => 'Refund', 'value' => 'refund'],
                                            ['name' => 'Replacement', 'value' => 'replacement'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The created RMA custom field.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 4,
                                    'code' => 'preferred_resolution',
                                    'label' => 'Preferred resolution',
                                    'type' => 'select',
                                    'isRequired' => 1,
                                    'position' => 1,
                                    'inputValidation' => null,
                                    'status' => 1,
                                    'options' => [
                                        ['id' => 11, 'name' => 'Refund', 'value' => 'refund'],
                                        ['id' => 12, 'name' => 'Replacement', 'value' => 'replacement'],
                                    ],
                                    'message' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-20T09:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/rma/custom-fields/{id}',
            requirements: ['id' => '\d+'],
            input: AdminRmaCustomFieldUpdateInput::class,
            provider: AdminRmaCustomFieldWriteProvider::class,
            processor: AdminRmaCustomFieldProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Update an RMA custom field',
                description: 'Partial update. Sending `options` replaces the full option set. Permission: sales.rma.custom-fields.edit.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'label' => ['type' => 'string', 'example' => 'How should we resolve this?'],
                                    'is_required' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'position' => ['type' => 'integer', 'example' => 2],
                                    'status' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'options' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'name' => ['type' => 'string'],
                                                'value' => ['type' => 'string'],
                                            ],
                                        ],
                                        'example' => [
                                            ['name' => 'Refund', 'value' => 'refund'],
                                            ['name' => 'Store credit', 'value' => 'store_credit'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The updated RMA custom field.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 4,
                                    'code' => 'preferred_resolution',
                                    'label' => 'How should we resolve this?',
                                    'type' => 'select',
                                    'isRequired' => 0,
                                    'position' => 2,
                                    'inputValidation' => null,
                                    'status' => 1,
                                    'options' => [
                                        ['id' => 13, 'name' => 'Refund', 'value' => 'refund'],
                                        ['id' => 14, 'name' => 'Store credit', 'value' => 'store_credit'],
                                    ],
                                    'message' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-20T11:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/rma/custom-fields/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaCustomFieldWriteProvider::class,
            processor: AdminRmaCustomFieldProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Delete an RMA custom field',
                description: 'Permission: sales.rma.custom-fields.delete.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA custom field was deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'RMA custom field deleted successfully.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminRmaCustomFieldCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'code' => ['type' => 'String'],
                'label' => ['type' => 'String'],
                'type' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
        new Query(provider: AdminRmaCustomFieldItemProvider::class),
        new Mutation(name: 'create', input: AdminRmaCustomFieldCreateInput::class, processor: AdminRmaCustomFieldProcessor::class),
        new Mutation(name: 'update', input: AdminRmaCustomFieldUpdateInput::class, processor: AdminRmaCustomFieldProcessor::class),
        new Mutation(name: 'delete', input: AdminRmaCustomFieldUpdateInput::class, processor: AdminRmaCustomFieldProcessor::class),
    ],
)]
class AdminRmaCustomField
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $code = null;

    public ?string $label = null;

    public ?string $type = null;

    public ?int $is_required = null;

    public ?int $position = null;

    public ?string $input_validation = null;

    public ?int $status = null;

    /** @var array<int,array{id:int,name:string,value:string}>|null */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $options = null;

    public ?string $message = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
