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
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'List RMA custom fields', description: 'Extra fields the return form collects. Filters: code (LIKE), label (LIKE), type, status. Sort: id (default desc), position, code.'),
        ),
        new Get(
            uriTemplate: '/rma/custom-fields/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaCustomFieldItemProvider::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Get an RMA custom field (with its options)'),
        ),
        new Post(
            uriTemplate: '/rma/custom-fields',
            input: AdminRmaCustomFieldCreateInput::class,
            processor: AdminRmaCustomFieldProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Create an RMA custom field', description: 'Body `{ code, label, position, type, is_required, input_validation, status, options: [{ name, value }] }`. `options` required for select/multiselect/checkbox/radio. Permission: sales.rma.custom-fields.create.'),
        ),
        new Put(
            uriTemplate: '/rma/custom-fields/{id}',
            requirements: ['id' => '\d+'],
            input: AdminRmaCustomFieldUpdateInput::class,
            provider: AdminRmaCustomFieldWriteProvider::class,
            processor: AdminRmaCustomFieldProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Update an RMA custom field', description: 'Permission: sales.rma.custom-fields.edit.'),
        ),
        new Delete(
            uriTemplate: '/rma/custom-fields/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaCustomFieldWriteProvider::class,
            processor: AdminRmaCustomFieldProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Delete an RMA custom field', description: 'Permission: sales.rma.custom-fields.delete.'),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminRmaCustomFieldCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'code'   => ['type' => 'String'],
                'label'  => ['type' => 'String'],
                'type'   => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort'   => ['type' => 'String'],
                'order'  => ['type' => 'String'],
                'first'  => ['type' => 'Int'],
                'after'  => ['type' => 'String'],
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
