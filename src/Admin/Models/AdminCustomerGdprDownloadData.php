<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGdprDownloadDataInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
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
            uriVariables: [
                'customerId' => new Link(parameterName: 'customerId', fromClass: AdminCustomerGdprDownloadData::class, identifiers: ['id']),
            ],
            input: false,
            processor: AdminCustomerGdprDownloadDataProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer GDPR'],
                summary: 'Download a GDPR data export for a customer',
                description: 'Returns a JSON dump of every table referencing the customer\'s id (orders, addresses, reviews, wishlists, notes). No request body — the customer is identified by the path.',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'GDPR data export for the customer.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'customerId'    => 14,
                                    'customerEmail' => 'jane@example.com',
                                    'generatedAt'   => '2026-06-24T10:15:00+00:00',
                                    'data'          => [
                                        'customer'  => ['id' => 14, 'firstName' => 'Jane', 'lastName' => 'Doe', 'email' => 'jane@example.com'],
                                        'addresses' => [['id' => 31, 'city' => 'Mountain View', 'country' => 'US', 'postcode' => '94043']],
                                        'orders'    => [['id' => 1042, 'incrementId' => '1042', 'grandTotal' => 4000, 'status' => 'completed']],
                                        'reviews'   => [['id' => 21, 'productId' => 2358, 'rating' => 5, 'status' => 'approved']],
                                        'wishlist'  => [['id' => 88, 'productId' => 2358]],
                                        'notes'     => [['id' => 7, 'note' => 'Called the customer about delivery.']],
                                    ],
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
            input: AdminCustomerGdprDownloadDataInput::class,
            processor: AdminCustomerGdprDownloadDataProcessor::class,
            description: 'Download GDPR data dump. Becomes createAdminCustomerGdprDownloadData.',
        ),
    ],
)]
class AdminCustomerGdprDownloadData
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    #[ApiProperty(writable: false)]
    public ?string $generated_at = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $data = null;
}
