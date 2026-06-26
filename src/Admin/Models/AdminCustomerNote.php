<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerNoteCreateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerNoteCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerNoteProcessor;

/**
 * Customer Notes — append-only.
 *
 * REST    : POST /api/admin/customers/{customerId}/notes
 * GraphQL : createAdminCustomerNote
 *
 * Mirrors CustomerController::storeNotes — writes into the `customer_notes`
 * table (a separate table; the legacy `customers.notes` text column was
 * dropped in 2023). One row per note; never overwrites.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerNote',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/customers/{customerId}/notes',
            provider: AdminCustomerNoteCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "List a customer's notes",
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Notes for the customer, newest first.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'                => 7, 'note' => 'Called the customer about delivery.',
                                            'customerId'        => 1, 'customerNotified' => false,
                                            'createdAt'         => '2026-06-09T10:15:00+00:00',
                                        ],
                                    ],
                                    'meta' => ['currentPage' => 1, 'perPage' => 1, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/customers/{customerId}/notes',
            uriVariables: [
                'customerId' => new Link(parameterName: 'customerId', fromClass: AdminCustomerNote::class, identifiers: ['id']),
            ],
            input: AdminCustomerNoteCreateInput::class,
            processor: AdminCustomerNoteProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Add a note to a customer',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['note'],
                                'properties' => [
                                    'note'              => ['type' => 'string'],
                                    'customer_notified' => ['type' => 'boolean'],
                                ],
                            ],
                            'example' => [
                                'note'              => 'Called the customer about delivery.',
                                'customer_notified' => false,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Note added.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'               => 7,
                                    'note'             => 'Called the customer about delivery.',
                                    'customerId'       => 14,
                                    'customerNotified' => false,
                                    'createdAt'        => '2026-06-24T10:15:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Empty note.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCustomerNoteCollectionProvider::class,
            paginationType: 'cursor',
            description: "A customer's notes, newest first. Becomes adminCustomerNotes.",
            args: ['customerId' => ['type' => 'Int!', 'description' => 'Customer ID']],
        ),
        new Mutation(
            name: 'create',
            input: AdminCustomerNoteCreateInput::class,
            processor: AdminCustomerNoteProcessor::class,
            description: 'Append a note to a customer. Becomes createAdminCustomerNote.',
        ),
    ],
)]
class AdminCustomerNote
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    #[ApiProperty(writable: false)]
    public ?string $note = null;

    #[ApiProperty(writable: false)]
    public ?bool $customer_notified = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;
}
