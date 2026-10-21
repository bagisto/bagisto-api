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
    shortName: 'AdminLogout',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/auth/logout',
            input: AdminLogoutInput::class,
            output: self::class,
            processor: AdminLogoutProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Log out of the current session',
                description: 'Revokes the session the access token belongs to, so its access and refresh tokens stop working at once. Needs a login session access token, not an API token.',
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
                    '200' => new Model\Response(description: 'Logged out.'),
                    '401' => new Model\Response(description: 'Missing or invalid token.'),
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
            description: 'Log out of the current session. Becomes createAdminLogout.',
        ),
    ],
)]
class AdminLogout
{
    /**
     * Whether the logout succeeded.
     */
    #[ApiProperty(writable: false, example: true)]
    public ?bool $success = null;

    /**
     * The confirmation message.
     */
    #[ApiProperty(writable: false, example: 'You have been logged out.')]
    public ?string $message = null;
}
