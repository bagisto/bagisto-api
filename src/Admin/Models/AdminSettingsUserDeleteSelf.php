<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsUserDeleteSelfInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserDeleteSelfProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsUserDeleteSelf',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/users/delete-self',
            input: AdminSettingsUserDeleteSelfInput::class,
            processor: AdminSettingsUserDeleteSelfProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Users'],
                summary: 'Delete your own admin account (password-confirmed)',
                description: 'Deletes the authenticated admin (the owner of the calling token) after re-confirming their password. Refuses if this is the last remaining admin (400) or the password is wrong (422).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['password'],

                                'properties' => [
                                    'password' => [
                                        'type' => 'string',
                                        'example' => 'current-password',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Account deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Your admin account has been deleted.',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Last remaining admin cannot be deleted.'),
                    '422' => new Model\Response(description: 'Missing or incorrect password.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminSettingsUserDeleteSelfInput::class,
            processor: AdminSettingsUserDeleteSelfProcessor::class,
            description: 'Delete your own admin account (password-confirmed). Becomes createAdminSettingsUserDeleteSelf.',
        ),
    ],
)]
class AdminSettingsUserDeleteSelf
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The success.
     */
    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
