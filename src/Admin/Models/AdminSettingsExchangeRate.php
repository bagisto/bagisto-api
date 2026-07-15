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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsExchangeRateCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsExchangeRateUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateWriteProvider;

/**
 * Admin Settings → Exchange Rates endpoints (Block B Wave 1).
 *
 * REST:
 *   GET    /api/admin/settings/exchange-rates
 *   GET    /api/admin/settings/exchange-rates/{id}
 *   POST   /api/admin/settings/exchange-rates
 *   PUT    /api/admin/settings/exchange-rates/{id}
 *   DELETE /api/admin/settings/exchange-rates/{id}
 *
 * GraphQL: adminSettingsExchangeRates, adminSettingsExchangeRate,
 *          createAdminSettingsExchangeRate, updateAdminSettingsExchangeRate,
 *          deleteAdminSettingsExchangeRate
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\ExchangeRateController.
 *
 * Notes:
 *  - Bagisto's `currency_exchange_rates` table has no `source_currency` column.
 *    The source currency is implicit (the channel's base currency); only the
 *    target_currency + rate pair is stored. Composite uniqueness is therefore
 *    enforced on `target_currency` alone.
 *  - The admin UI's "Mass Auto-Sync" action (calls an external exchange-rates
 *    helper to refresh every row in bulk) is intentionally NOT exposed in v1.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsExchangeRate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/exchange-rates',
            input: AdminSettingsExchangeRateCreateInput::class,
            processor: AdminSettingsExchangeRateProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Exchange Rates'],
                summary: 'Create a new exchange rate',
                description: 'Creates a target_currency → rate mapping. Composite uniqueness is enforced on target_currency (one exchange rate per currency).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['target_currency', 'rate'],
                                'properties' => [
                                    'target_currency' => ['type' => 'integer', 'example' => 2, 'description' => 'Currency ID (target).'],
                                    'rate' => ['type' => 'number', 'format' => 'float', 'example' => 1.085],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Exchange rate created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 4,
                                    'targetCurrency' => 2,
                                    'targetCurrencyCode' => 'EUR',
                                    'targetCurrencyName' => 'Euro',
                                    'rate' => 1.085,
                                    'createdAt' => '2026-05-22T08:15:00+00:00',
                                    'updatedAt' => '2026-05-22T08:15:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Validation failure (missing fields, unknown currency, duplicate pair, non-positive rate).',
                    ),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/exchange-rates/{id}',
            input: AdminSettingsExchangeRateUpdateInput::class,
            provider: AdminSettingsExchangeRateWriteProvider::class,
            processor: AdminSettingsExchangeRateProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Exchange Rates'],
                summary: 'Update an exchange rate',
                description: 'Updates target_currency and/or rate. Composite-uniqueness excludes self.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Exchange rate ID.', true, schema: ['type' => 'integer', 'example' => 4]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'target_currency' => ['type' => 'integer', 'example' => 2],
                                    'rate' => ['type' => 'number', 'format' => 'float', 'example' => 1.09],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Exchange rate updated.'),
                    '404' => new Model\Response(description: 'Exchange rate not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/exchange-rates/{id}',
            provider: AdminSettingsExchangeRateWriteProvider::class,
            processor: AdminSettingsExchangeRateProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Exchange Rates'],
                summary: 'Delete an exchange rate',
                parameters: [
                    new Model\Parameter('id', 'path', 'Exchange rate ID.', true, schema: ['type' => 'integer', 'example' => 4]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Exchange rate deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Exchange rate deleted successfully.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Exchange rate not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/exchange-rates/{id}',
            provider: AdminSettingsExchangeRateItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Exchange Rates'],
                summary: 'Exchange rate detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Exchange rate ID.', true, schema: ['type' => 'integer', 'example' => 4]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single exchange rate row.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 4,
                                    'targetCurrency' => 2,
                                    'targetCurrencyCode' => 'EUR',
                                    'targetCurrencyName' => 'Euro',
                                    'rate' => 1.085,
                                    'createdAt' => '2026-05-22T08:15:00+00:00',
                                    'updatedAt' => '2026-05-22T08:15:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Exchange rate not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/exchange-rates',
            provider: AdminSettingsExchangeRateCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Exchange Rates'],
                summary: 'List exchange rates',
                description: 'Paginated, filterable, sortable list of currency exchange rates. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('target_currency', 'query', 'Filter by target currency ID.', false, schema: ['type' => 'integer', 'example' => 2]),
                    new Model\Parameter('rate_from', 'query', 'Minimum rate (inclusive).', false, schema: ['type' => 'number', 'example' => 0.5]),
                    new Model\Parameter('rate_to', 'query', 'Maximum rate (inclusive).', false, schema: ['type' => 'number', 'example' => 2.0]),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'target_currency', 'rate'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in the { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsExchangeRateCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'target_currency' => ['type' => 'Int'],
                'rate_from' => ['type' => 'Float'],
                'rate_to' => ['type' => 'Float'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin settings exchange-rates listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsExchangeRateItemProvider::class,
            description: 'Admin settings exchange-rate detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsExchangeRateCreateInput::class,
            processor: AdminSettingsExchangeRateProcessor::class,
            description: 'Create a new exchange rate. Becomes createAdminSettingsExchangeRate.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsExchangeRateUpdateInput::class,
            processor: AdminSettingsExchangeRateProcessor::class,
            description: 'Update an exchange rate. Becomes updateAdminSettingsExchangeRate.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsExchangeRateUpdateInput::class,
            processor: AdminSettingsExchangeRateProcessor::class,
            description: 'Delete an exchange rate. Becomes deleteAdminSettingsExchangeRate.',
        ),
    ],
)]
class AdminSettingsExchangeRate
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false, example: 1)]
    public ?int $id = null;

    #[ApiProperty(writable: false, example: 2)]
    public ?int $target_currency = null;

    #[ApiProperty(writable: false, example: 'EUR')]
    public ?string $target_currency_code = null;

    #[ApiProperty(writable: false, example: 'Euro')]
    public ?string $target_currency_name = null;

    #[ApiProperty(writable: false, example: 0.92)]
    public ?float $rate = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:15:00+00:00')]
    public ?string $created_at = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:20:00+00:00')]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false, example: 'Exchange rate deleted successfully.')]
    public ?string $message = null;
}
