<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\LogoutInput;
use Webkul\BagistoApi\OpenApi\CustomerAuthResponses;
use Webkul\BagistoApi\State\CustomerLogoutAllProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerLogoutAll',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customer/logout-all',
            input: LogoutInput::class,
            output: self::class,
            processor: CustomerLogoutAllProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Customer Authentication'],
                summary: 'Log out of every session',
                description: 'Ends every session of the signed-in customer, this one included, on every device; their access and refresh tokens stop working at once. Needs a customer access token.',
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
                        description: 'Every session ended. `revoked` is how many sessions were ended.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'You have been logged out of every session.',
                                    'revoked' => 3,
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(
                        description: 'The access token is missing, invalid or its session already ended (`unauthenticated`), or it expired (`access_token_expired`).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => CustomerAuthResponses::UNAUTHENTICATED,
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
            input: LogoutInput::class,
            output: self::class,
            processor: CustomerLogoutAllProcessor::class,
            description: 'End every session of the signed-in customer. Becomes createCustomerLogoutAll; needs a customer access token.',
        ),
    ],
)]
class CustomerLogoutAll
{
    /**
     * Whether the sessions were ended.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;

    /**
     * How many sessions were ended.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?int $revoked = null;
}
