<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\State\AdminInvoiceMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminInvoiceMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/invoices/mass-update-status',
            input: AdminInvoiceMassUpdateStatusInput::class,
            processor: AdminInvoiceMassUpdateStatusProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Invoices'],
                summary: 'Mass update invoice status',
                description: 'Bulk-sets the status of a batch of invoices to pending, paid, or overdue. This is a manual status override — it does not capture or reverse a payment.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices', 'value'],
                                'properties' => [
                                    'indices' => [
                                        'type'    => 'array',
                                        'items'   => ['type' => 'integer'],
                                        'example' => [560, 561],
                                    ],
                                    'value' => [
                                        'type'    => 'string',
                                        'enum'    => ['pending', 'paid', 'overdue'],
                                        'example' => 'paid',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Invoices updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'updated' => [560, 561],
                                    'message' => 'Invoice status updated successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Empty indices, or value not one of pending/paid/overdue.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.invoices.view.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'create',
            input: AdminInvoiceMassUpdateStatusInput::class,
            processor: AdminInvoiceMassUpdateStatusProcessor::class,
            description: 'Mass-update status for a batch of invoices. Becomes createAdminInvoiceMassUpdateStatus.',
        ),
    ],
)]
class AdminInvoiceMassUpdateStatus
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $updated = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
