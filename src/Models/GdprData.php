<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\GdprDataQueryResolver;
use Webkul\BagistoApi\State\GdprDataProvider;

/**
 * The signed-in customer's personal data, as the storefront GDPR page shows it.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GdprData',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/gdpr-data',
            provider: GdprDataProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['GDPR'],
                summary: 'Download my data',
                description: 'The personal data the store holds about the signed-in customer, as on the storefront GDPR page: profile, saved addresses and orders. Returned as a list of one entry. For the same data as a PDF, call `GET /api/shop/gdpr-data/pdf` with `Accept: application/pdf`. Needs Configuration > General > GDPR enabled.',
                responses: [
                    '200' => new Response(
                        description: 'The customer data.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 3,
                                        'customer' => [
                                            'firstName' => 'Paul',
                                            'lastName' => 'Doe',
                                            'email' => 'pauldoe@example.com',
                                            'gender' => 'Male',
                                            'dateOfBirth' => '1990-04-12',
                                            'phone' => '2125551234',
                                        ],
                                        'addresses' => [
                                            [
                                                'firstName' => 'Paul',
                                                'lastName' => 'Doe',
                                                'companyName' => null,
                                                'address' => '123 Main St',
                                                'city' => 'Los Angeles',
                                                'state' => 'CA',
                                                'country' => 'US',
                                                'postcode' => '90001',
                                                'phone' => '2125551234',
                                                'vatId' => null,
                                            ],
                                        ],
                                        'orders' => [
                                            [
                                                'id' => 7,
                                                'incrementId' => '7',
                                                'status' => 'completed',
                                                'shippingTitle' => 'Flat Rate - Flat Rate',
                                                'grandTotal' => 109,
                                                'formattedGrandTotal' => '$109.00',
                                                'createdAt' => '2026-10-15 20:11:24',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Response(description: 'GDPR is switched off.'),
                    '401' => new Response(description: 'Not signed in.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: GdprDataQueryResolver::class,
            args: [],
            description: 'The personal data the store holds about the signed-in customer.',
        ),
    ],
)]
class GdprData
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(writable: false, schema: ['type' => 'object', 'additionalProperties' => true])]
    public ?array $customer = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ApiProperty(writable: false, schema: ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]])]
    public ?array $addresses = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ApiProperty(writable: false, schema: ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]])]
    public ?array $orders = null;
}
