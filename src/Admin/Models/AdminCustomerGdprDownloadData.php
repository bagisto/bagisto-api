<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGdprDownloadDataInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprDownloadDataProcessor;

/**
 * GDPR data export — JSON dump of every table referencing the customer's id.
 *
 * REST    : POST /api/admin/customers/{customerId}/gdpr-download-data
 * GraphQL : createAdminCustomerGdprDownloadData
 *
 * Not bound to a GDPR request — admin can run ad-hoc on any customer.
 * Permission: customers.gdpr_requests.view (read-only inspection).
 *
 * Returns an embedded `data` array carrying:
 *   - customer:  full customer record
 *   - addresses: every customer-address row
 *   - orders:    every order with items + addresses + payment
 *   - reviews:   product reviews authored by the customer
 *   - wishlist:  wishlist items
 *   - notes:     admin notes
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerGdprDownloadData',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/{customerId}/gdpr-download-data',
            input: AdminCustomerGdprDownloadDataInput::class,
            processor: AdminCustomerGdprDownloadDataProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer GDPR'],
                summary: 'Download a GDPR data export for a customer',
                description: 'Returns a JSON dump of every table referencing the customer\'s id (orders, addresses, reviews, wishlists, notes).',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCustomerGdprDownloadDataInput::class,
            processor: AdminCustomerGdprDownloadDataProcessor::class,
            description: 'Download GDPR data dump. Becomes createAdminCustomerGdprDownloadData.',
        ),
    ],
)]
class AdminCustomerGdprDownloadData
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(writable: false)]
    public ?string $customerEmail = null;

    #[ApiProperty(writable: false)]
    public ?string $generatedAt = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $data = null;
}
