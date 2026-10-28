<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminLogoutInput;
use Webkul\BagistoApi\Admin\State\AdminLogoutProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminLogoutAll',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/auth/logout-all',
            input: AdminLogoutInput::class,
            output: self::class,
            processor: AdminLogoutProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Log out of every session',
                description: 'Revokes every session of the signed-in admin, on every device, including the current one. Needs a login session access token, not an API token.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Logged out of every session; `revoked` is how many sessions were ended, the current one included.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',

                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'message' => ['type' => 'string'],

                                        'revoked' => [
                                            'type' => 'integer',
                                            'description' => 'How many sessions were revoked.',
                                        ],
                                    ],
                                ],
                                'example' => [
                                    'success' => true,
                                    'message' => 'You have been logged out of every session.',
                                    'revoked' => 3,
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'The access token is missing or invalid, or its session was already revoked (`unauthenticated`), or it expired (`access_token_expired`).'),
                    '403' => new Model\Response(description: 'Called with an API token (`access_token_required`).'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminLogoutInput::class,
            output: self::class,
            processor: AdminLogoutProcessor::class,
            description: 'Log out of every session of the signed-in admin. Becomes createAdminLogoutAll.',
        ),
    ],
)]
class AdminLogoutAll
{
    /**
     * Whether the logout succeeded.
     */
    #[ApiProperty(writable: false, example: true)]
    public ?bool $success = null;

    /**
     * The confirmation message.
     */
    #[ApiProperty(writable: false, example: 'You have been logged out of every session.')]
    public ?string $message = null;

    /**
     * How many sessions were revoked.
     */
    #[ApiProperty(writable: false, example: 3)]
    public ?int $revoked = null;
}
