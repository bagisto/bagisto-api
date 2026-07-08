<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGdprProcessInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprProcessProcessor;

/**
 * Approve + execute a GDPR request (destructive action for delete requests).
 *
 * REST    : POST /api/admin/customers/gdpr-requests/{id}/process
 * GraphQL : createAdminCustomerGdprProcess
 *
 * For type=delete: cascades the customer delete (CustomerRepository::delete()
 * fires the customer.delete.before / after events that the GDPR module already
 * listens to).
 *
 * For type=update: marks the request approved. The admin then applies the
 * pending profile changes manually — there is no embedded update payload in
 * gdpr_data_request, only a free-form `message`.
 *
 * Permission: customers.gdpr_requests.edit.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerGdprProcess',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/gdpr-requests/{id}/process',
            input: AdminCustomerGdprProcessInput::class,
            processor: AdminCustomerGdprProcessProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer GDPR'],
                summary: 'Process (approve + execute) a GDPR request',
                description: 'Sets status to "approved" and, for type=delete requests, cascades the customer deletion. Refuses to re-process an already-approved request.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Request ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'message' => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                            'example' => ['message' => 'Approved and processed per customer request.'],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'GDPR request processed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 9, 'requestId' => 9, 'customerId' => 14,
                                    'type' => 'delete', 'status' => 'approved', 'customerDeleted' => true,
                                    'processedAt' => '2026-06-24T10:15:00+00:00',
                                    'message' => 'GDPR request processed successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Request already approved or revoked.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCustomerGdprProcessInput::class,
            processor: AdminCustomerGdprProcessProcessor::class,
            description: 'Approve + execute a GDPR request. Becomes createAdminCustomerGdprProcess.',
        ),
    ],
)]
class AdminCustomerGdprProcess
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $request_id = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?bool $customer_deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $processed_at = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
