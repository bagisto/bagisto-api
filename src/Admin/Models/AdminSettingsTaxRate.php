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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxRateCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxRateUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateExportProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateWriteProvider;

/**
 * Admin Settings → Tax Rates endpoints (Block B Wave 3).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\Tax\TaxRateController.
 *
 * is_zip conditional validation:
 *   - is_zip = false → zip_code required (the specific zip mode)
 *   - is_zip = true  → zip_from + zip_to required (the range mode)
 *
 * No mass-delete in the monolith admin UI; not exposed.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsTaxRate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/tax-rates',
            input: AdminSettingsTaxRateCreateInput::class,
            processor: AdminSettingsTaxRateProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Rates'],
                summary: 'Create a new tax rate',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['identifier', 'country', 'tax_rate', 'is_zip'],
                                'properties' => [
                                    'identifier' => ['type' => 'string', 'example' => 'US-CA-SF'],
                                    'is_zip' => ['type' => 'boolean', 'example' => false],
                                    'zip_code' => ['type' => 'string', 'example' => '94103'],
                                    'zip_from' => ['type' => 'string', 'example' => '94100'],
                                    'zip_to' => ['type' => 'string', 'example' => '94199'],
                                    'state' => ['type' => 'string', 'example' => 'CA'],
                                    'country' => ['type' => 'string', 'example' => 'US'],
                                    'tax_rate' => ['type' => 'number', 'format' => 'float', 'example' => 8.5],
                                ],
                            ],
                            'examples' => [
                                'specific-zip' => [
                                    'summary' => 'Specific zip (is_zip=false)',
                                    'value' => ['identifier' => 'US-CA-SF', 'is_zip' => false, 'zip_code' => '94103', 'state' => 'CA', 'country' => 'US', 'tax_rate' => 8.5],
                                ],
                                'zip-range' => [
                                    'summary' => 'Zip range (is_zip=true)',
                                    'value' => ['identifier' => 'US-CA-BAY', 'is_zip' => true, 'zip_from' => '94000', 'zip_to' => '94999', 'state' => 'CA', 'country' => 'US', 'tax_rate' => 8.25],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Tax rate created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 4,
                                    'identifier' => 'US-CA-SF',
                                    'isZip' => false,
                                    'zipCode' => '94103',
                                    'zipFrom' => null,
                                    'zipTo' => null,
                                    'state' => 'CA',
                                    'country' => 'US',
                                    'taxRate' => 8.5,
                                    'createdAt' => '2026-05-25T08:15:00+00:00',
                                    'updatedAt' => '2026-05-25T08:15:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure (missing field, duplicate identifier, conditional zip rules).'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/tax-rates/{id}',
            input: AdminSettingsTaxRateUpdateInput::class,
            provider: AdminSettingsTaxRateWriteProvider::class,
            processor: AdminSettingsTaxRateProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Rates'],
                summary: 'Update a tax rate',
                description: 'Partial update; identifier uniqueness excludes self.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Tax rate ID.', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Tax rate updated.'),
                    '404' => new Model\Response(description: 'Tax rate not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/tax-rates/{id}',
            provider: AdminSettingsTaxRateWriteProvider::class,
            processor: AdminSettingsTaxRateProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Rates'],
                summary: 'Delete a tax rate',
                parameters: [
                    new Model\Parameter('id', 'path', 'Tax rate ID.', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Tax rate deleted (pivot to tax_categories cascades).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Tax rate deleted successfully.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Tax rate not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/tax-rates/{id}',
            provider: AdminSettingsTaxRateItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Rates'],
                summary: 'Tax rate detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Tax rate ID.', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single tax rate row.'),
                    '404' => new Model\Response(description: 'Tax rate not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/tax-rates',
            provider: AdminSettingsTaxRateCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Rates'],
                summary: 'List tax rates',
                description: 'Paginated, filterable, sortable list. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('identifier', 'query', 'Partial match on identifier.', false, schema: ['type' => 'string']),
                    new Model\Parameter('country', 'query', 'Country code exact.', false, schema: ['type' => 'string']),
                    new Model\Parameter('state', 'query', 'State exact.', false, schema: ['type' => 'string']),
                    new Model\Parameter('tax_rate_from', 'query', 'Minimum tax rate (inclusive).', false, schema: ['type' => 'number']),
                    new Model\Parameter('tax_rate_to', 'query', 'Maximum tax rate (inclusive).', false, schema: ['type' => 'number']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'identifier', 'tax_rate']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in the { data, meta } envelope.'),
                ],
            ),
        ),
        // Export MUST stay the LAST Get op so the GraphQL node-id IRI keeps
        // resolving from the detail Get (/settings/tax-rates/{id}), not /export.
        new Get(
            uriTemplate: '/settings/tax-rates/export',
            provider: AdminSettingsTaxRateExportProvider::class,
            outputFormats: ['csv' => ['text/csv'], 'xls' => ['application/vnd.ms-excel'], 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Rates'],
                summary: 'Export tax rates as csv, xls or xlsx',
                description: 'Streams the tax-rate datagrid as a csv, xls or xlsx download (the admin Export button). Honours the same filters as the listing. REST only; send the Accept header matching the requested format.',
                parameters: [
                    new Model\Parameter('format', 'query', 'Export format: csv, xls or xlsx. Defaults to csv.', false, schema: ['type' => 'string', 'enum' => ['csv', 'xls', 'xlsx'], 'example' => 'csv']),
                    new Model\Parameter('identifier', 'query', 'Partial match on identifier.', false, schema: ['type' => 'string']),
                    new Model\Parameter('country', 'query', 'Country code exact.', false, schema: ['type' => 'string']),
                    new Model\Parameter('state', 'query', 'State exact.', false, schema: ['type' => 'string']),
                    new Model\Parameter('tax_rate_from', 'query', 'Minimum tax rate (inclusive).', false, schema: ['type' => 'number']),
                    new Model\Parameter('tax_rate_to', 'query', 'Maximum tax rate (inclusive).', false, schema: ['type' => 'number']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Export file downloaded as an attachment (tax-rates.csv, .xls or .xlsx).'),
                    '422' => new Model\Response(description: 'Unsupported format (csv, xls and xlsx only).'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsTaxRateCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'identifier' => ['type' => 'String'],
                'country' => ['type' => 'String'],
                'state' => ['type' => 'String'],
                'tax_rate_from' => ['type' => 'Float'],
                'tax_rate_to' => ['type' => 'Float'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin settings tax-rates listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsTaxRateItemProvider::class,
            description: 'Admin settings tax-rate detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsTaxRateCreateInput::class,
            processor: AdminSettingsTaxRateProcessor::class,
            description: 'Create a new tax rate. Becomes createAdminSettingsTaxRate.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsTaxRateUpdateInput::class,
            processor: AdminSettingsTaxRateProcessor::class,
            description: 'Update a tax rate. Becomes updateAdminSettingsTaxRate.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsTaxRateUpdateInput::class,
            processor: AdminSettingsTaxRateProcessor::class,
            description: 'Delete a tax rate. Becomes deleteAdminSettingsTaxRate.',
        ),
    ],
)]
class AdminSettingsTaxRate
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false, example: 1)]
    public ?int $id = null;

    #[ApiProperty(writable: false, example: 'US-CA-SF')]
    public ?string $identifier = null;

    #[ApiProperty(writable: false, example: false)]
    public ?bool $is_zip = null;

    #[ApiProperty(writable: false, example: '94103')]
    public ?string $zip_code = null;

    #[ApiProperty(writable: false, example: null)]
    public ?string $zip_from = null;

    #[ApiProperty(writable: false, example: null)]
    public ?string $zip_to = null;

    #[ApiProperty(writable: false, example: 'CA')]
    public ?string $state = null;

    #[ApiProperty(writable: false, example: 'US')]
    public ?string $country = null;

    #[ApiProperty(writable: false, example: 8.5)]
    public ?float $tax_rate = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:15:00+00:00')]
    public ?string $created_at = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:20:00+00:00')]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false, example: 'Tax rate deleted successfully.')]
    public ?string $message = null;
}
