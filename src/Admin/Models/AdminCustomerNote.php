<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerNoteCreateInput;
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
        new Post(
            uriTemplate: '/customers/{customerId}/notes',
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
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
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
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(writable: false)]
    public ?string $note = null;

    #[ApiProperty(writable: false)]
    public ?bool $customerNotified = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;
}
