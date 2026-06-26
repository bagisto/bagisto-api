<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerImpersonateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerImpersonateProcessor;

/**
 * Login-as-customer impersonation.
 *
 * Issues a Sanctum customer token bound to the target customer; the token
 * carries an `impersonatedByAdminId` ability for audit. Expires in 1 hour.
 *
 * REST    : POST /api/admin/customers/{customerId}/impersonate
 * GraphQL : createAdminCustomerImpersonate
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerImpersonate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/{customerId}/impersonate',
            uriVariables: [
                'customerId' => new Link(parameterName: 'customerId', fromClass: AdminCustomerImpersonate::class, identifiers: ['id']),
            ],
            input: false,
            processor: AdminCustomerImpersonateProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Issue an impersonation token for a customer',
                description: 'Returns a Sanctum customer token that the admin can use to act as the customer. The token expires in 1 hour and is audited as having been issued by the calling admin. No request body — the customer is identified by the path.',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '201' => new Model\Response(
                        description: 'Impersonation token issued.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'token'                 => '42|q7Xz9aB3cD5eF7gH9iJ1kL3mN5oP7qR9sT1uV3w',
                                    'customerId'            => 14,
                                    'customerEmail'         => 'jane@example.com',
                                    'customerName'          => 'Jane Doe',
                                    'impersonatedByAdminId' => 1,
                                    'expiresAt'             => '2026-06-24T11:15:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCustomerImpersonateInput::class,
            processor: AdminCustomerImpersonateProcessor::class,
        ),
    ],
)]
class AdminCustomerImpersonate
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $token = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    #[ApiProperty(writable: false)]
    public ?int $impersonated_by_admin_id = null;

    #[ApiProperty(writable: false)]
    public ?string $expires_at = null;
}
