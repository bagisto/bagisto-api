<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerImpersonateInput;
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
            input: AdminCustomerImpersonateInput::class,
            processor: AdminCustomerImpersonateProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Issue an impersonation token for a customer',
                description: 'Returns a Sanctum customer token that the admin can use to act as the customer. The token expires in 1 hour and is audited as having been issued by the calling admin.',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
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
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $token = null;

    #[ApiProperty(writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(writable: false)]
    public ?string $customerEmail = null;

    #[ApiProperty(writable: false)]
    public ?string $customerName = null;

    #[ApiProperty(writable: false)]
    public ?int $impersonatedByAdminId = null;

    #[ApiProperty(writable: false)]
    public ?string $expiresAt = null;
}
