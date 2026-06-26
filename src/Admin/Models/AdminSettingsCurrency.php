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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsCurrencyCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsCurrencyUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyWriteProvider;

/**
 * Admin Settings → Currencies endpoints (Block B Wave 1).
 *
 * REST:
 *   GET    /api/admin/settings/currencies            — datagrid-parity listing
 *   GET    /api/admin/settings/currencies/{id}       — detail
 *   POST   /api/admin/settings/currencies            — create
 *   PUT    /api/admin/settings/currencies/{id}       — update
 *   DELETE /api/admin/settings/currencies/{id}       — delete (guards: last currency, channel base)
 *
 * GraphQL:
 *   adminSettingsCurrencies          — cursor listing
 *   adminSettingsCurrency(id:)       — detail
 *   createAdminSettingsCurrency      — create
 *   updateAdminSettingsCurrency      — update
 *   deleteAdminSettingsCurrency      — delete
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\CurrencyController 1:1.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsCurrency',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/currencies',
            input: AdminSettingsCurrencyCreateInput::class,
            processor: AdminSettingsCurrencyProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Currencies'],
                summary: 'Create a new currency',
                description: 'Mirrors Bagisto admin Settings → Currencies → Create. Validates code (required, alpha, exactly 3 chars, unique), name (required). Code is uppercased by the model mutator.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['code', 'name'],
                                'properties' => [
                                    'code'              => ['type' => 'string', 'example' => 'EUR'],
                                    'name'              => ['type' => 'string', 'example' => 'Euro'],
                                    'symbol'            => ['type' => 'string', 'example' => '€'],
                                    'decimal'           => ['type' => 'integer', 'example' => 2],
                                    'group_separator'   => ['type' => 'string', 'example' => ','],
                                    'decimal_separator' => ['type' => 'string', 'example' => '.'],
                                    'currency_position' => ['type' => 'string', 'example' => 'left'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Currency created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'               => 5,
                                    'code'             => 'EUR',
                                    'name'             => 'Euro',
                                    'symbol'           => '€',
                                    'decimal'          => 2,
                                    'groupSeparator'   => ',',
                                    'decimalSeparator' => '.',
                                    'currencyPosition' => 'left',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/currencies/{id}',
            input: AdminSettingsCurrencyUpdateInput::class,
            provider: AdminSettingsCurrencyWriteProvider::class,
            processor: AdminSettingsCurrencyProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Currencies'],
                summary: 'Update a currency',
                description: 'Validates name (required). Code is immutable in the monolith UI; if sent here it will be ignored by the repository payload filter.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Currency ID.', true, schema: ['type' => 'integer', 'example' => 5]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name'],
                                'properties' => [
                                    'name'              => ['type' => 'string', 'example' => 'Euro'],
                                    'symbol'            => ['type' => 'string', 'example' => '€'],
                                    'decimal'           => ['type' => 'integer', 'example' => 2],
                                    'group_separator'   => ['type' => 'string', 'example' => ','],
                                    'decimal_separator' => ['type' => 'string', 'example' => '.'],
                                    'currency_position' => ['type' => 'string', 'example' => 'left'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Currency updated.'),
                    '404' => new Model\Response(description: 'Currency not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/currencies/{id}',
            provider: AdminSettingsCurrencyWriteProvider::class,
            processor: AdminSettingsCurrencyProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Currencies'],
                summary: 'Delete a currency',
                description: 'Refuses with HTTP 400 if this is the only remaining currency, or if any channel uses it as its base_currency_id.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Currency ID.', true, schema: ['type' => 'integer', 'example' => 5]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Currency deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Currency deleted successfully.'],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Cannot delete — last remaining currency or a channel base currency.'),
                    '404' => new Model\Response(description: 'Currency not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/currencies/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsCurrencyItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Currencies'],
                summary: 'Currency detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Currency ID.', true, schema: ['type' => 'integer', 'example' => 5]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single currency.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'               => 5,
                                    'code'             => 'EUR',
                                    'name'             => 'Euro',
                                    'symbol'           => '€',
                                    'decimal'          => 2,
                                    'groupSeparator'   => ',',
                                    'decimalSeparator' => '.',
                                    'currencyPosition' => 'left',
                                    'createdAt'        => '2026-01-12T08:15:00+00:00',
                                    'updatedAt'        => '2026-04-30T14:20:09+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Currency not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/currencies',
            provider: AdminSettingsCurrencyCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Currencies'],
                summary: 'List currencies (datagrid parity)',
                description: 'Paginated, filterable, sortable currencies list. Filters: code, name, symbol. Sort: id, code, name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('code', 'query', 'Partial code match (SQL LIKE).', false, schema: ['type' => 'string', 'example' => 'EUR']),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string', 'example' => 'Euro']),
                    new Model\Parameter('symbol', 'query', 'Partial symbol match.', false, schema: ['type' => 'string', 'example' => '€']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'name'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of currency rows in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'               => 1,
                                            'code'             => 'USD',
                                            'name'             => 'US Dollar',
                                            'symbol'           => '$',
                                            'decimal'          => 2,
                                            'groupSeparator'   => ',',
                                            'decimalSeparator' => '.',
                                            'currencyPosition' => 'left',
                                            'createdAt'        => '2026-01-12T08:15:00+00:00',
                                            'updatedAt'        => '2026-04-30T14:20:09+00:00',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 10,
                                        'lastPage'    => 1,
                                        'total'       => 1,
                                        'from'        => 1,
                                        'to'          => 1,
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
            provider: AdminSettingsCurrencyCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'code'   => ['type' => 'String'],
                'name'   => ['type' => 'String'],
                'symbol' => ['type' => 'String'],
                'sort'   => ['type' => 'String'],
                'order'  => ['type' => 'String'],
            ],
            description: 'Admin currencies listing (cursor pagination). Mirrors REST GET /api/admin/settings/currencies.',
        ),
        new Query(
            provider: AdminSettingsCurrencyItemProvider::class,
            description: 'Admin currency detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsCurrencyCreateInput::class,
            processor: AdminSettingsCurrencyProcessor::class,
            description: 'Create a new currency. Becomes createAdminSettingsCurrency.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsCurrencyUpdateInput::class,
            processor: AdminSettingsCurrencyProcessor::class,
            description: 'Update a currency. Becomes updateAdminSettingsCurrency.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsCurrencyUpdateInput::class,
            processor: AdminSettingsCurrencyProcessor::class,
            description: 'Delete a currency. Becomes deleteAdminSettingsCurrency. Refused for the last currency or any channel base currency.',
        ),
    ],
)]
class AdminSettingsCurrency
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false, example: 1)]
    public ?int $id = null;

    #[ApiProperty(writable: false, example: 'USD')]
    public ?string $code = null;

    #[ApiProperty(writable: false, example: 'US Dollar')]
    public ?string $name = null;

    #[ApiProperty(writable: false, example: '$')]
    public ?string $symbol = null;

    #[ApiProperty(writable: false, example: 2)]
    public ?int $decimal = null;

    #[ApiProperty(writable: false, example: ',')]
    public ?string $group_separator = null;

    #[ApiProperty(writable: false, example: '.')]
    public ?string $decimal_separator = null;

    #[ApiProperty(writable: false, example: 'left')]
    public ?string $currency_position = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:15:00+00:00')]
    public ?string $created_at = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:20:00+00:00')]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false, example: 'Currency deleted successfully.')]
    public ?string $message = null;
}
