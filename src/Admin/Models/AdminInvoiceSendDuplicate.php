<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceSendDuplicateInput;
use Webkul\BagistoApi\Admin\State\AdminInvoiceSendDuplicateProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminInvoiceSendDuplicate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/invoices/{id}/send-duplicate',
            input: AdminInvoiceSendDuplicateInput::class,
            processor: AdminInvoiceSendDuplicateProcessor::class,
            status: 200,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Invoices'],
                summary: 'Send a duplicate invoice email',
                description: 'Emails a copy of the invoice. `email` is optional — defaults to the order\'s customer email. Requires `sales.invoices.view`.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => ['email' => 'customer@example.com'],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Invoice email sent.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 585,
                                    'email' => 'customer@example.com',
                                    'success' => true,
                                    'message' => 'Invoice email sent to customer@example.com.',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.invoices.view.'),
                    '404' => new Model\Response(description: 'Invoice not found.'),
                    '422' => new Model\Response(description: 'Recipient email is invalid.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminInvoiceSendDuplicateInput::class,
            processor: AdminInvoiceSendDuplicateProcessor::class,
            description: 'Send a duplicate invoice email. Becomes createAdminInvoiceSendDuplicate.',
        ),
    ],
)]
class AdminInvoiceSendDuplicate
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $email = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
