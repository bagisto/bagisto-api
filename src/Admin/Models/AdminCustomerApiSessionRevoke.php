<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerApiSessionRevokeInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerApiSessionRevokeProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerApiSessionRevoke',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/{customerId}/revoke-api-sessions',
            input: AdminCustomerApiSessionRevokeInput::class,
            output: self::class,
            processor: AdminCustomerApiSessionRevokeProcessor::class,
            requirements: ['customerId' => '\d+'],
            read: false,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customers: API Sessions'],
                summary: 'End every storefront session of a customer',
                description: 'Ends every live storefront session of the customer, on every device; their access and refresh tokens stop working at once and the customer has to log in again. Requires `customers.api_sessions.delete`.',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 1529,
                    ]),
                ],
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: '`revoked` is how many sessions were ended.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'customerId' => 1529,
                                    'revoked' => 2,
                                    'message' => 'Sessions revoked: 2.',
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'The role lacks `customers.api_sessions.delete`.'),
                    '404' => new Model\Response(description: 'No customer has this id.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCustomerApiSessionRevokeInput::class,
            output: self::class,
            processor: AdminCustomerApiSessionRevokeProcessor::class,
            description: 'End every storefront session of a customer. Becomes createAdminCustomerApiSessionRevoke; requires customers.api_sessions.delete.',
        ),
    ],
)]
class AdminCustomerApiSessionRevoke
{
    use AcceptsCamelCaseWrites;

    /**
     * The customer whose sessions were ended.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $customer_id = null;

    /**
     * How many sessions were ended.
     */
    #[ApiProperty(writable: false)]
    public ?int $revoked = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
