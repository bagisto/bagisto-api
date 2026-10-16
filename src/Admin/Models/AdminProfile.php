<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminAccountUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminProfileQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminAccountProcessor;
use Webkul\BagistoApi\Admin\State\AdminProfileProvider;

/**
 * The authenticated admin's own account: read it, edit it and manage its avatar,
 * mirroring the admin "My Account" screen.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminProfile',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/get',
            provider: AdminProfileProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Operation(
                tags: ['Admin Authentication'],
                summary: "Get logged-in admin user's details",
                description: 'Returns the profile of the currently authenticated admin. Requires a Bearer token.',
                responses: [
                    '200' => new Response(
                        description: "The authenticated admin's profile.",
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => '1',
                                        'name' => 'Example Admin',
                                        'email' => 'admin@example.com',
                                        'image' => null,
                                        'imageUrl' => null,
                                        'status' => '1',
                                        'roleId' => 1,
                                        'roleName' => 'Administrator',
                                        'success' => true,
                                        'message' => null,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/account',
            input: AdminAccountUpdateInput::class,
            processor: AdminAccountProcessor::class,
            read: false,
            deserialize: false,
            openapi: new Operation(
                tags: ['Admin Authentication'],
                summary: 'Update my account',
                description: 'Updates the authenticated admin\'s name, email and, optionally, password. `current_password` is required for every change. Send `password` with a matching `password_confirmation` to change the password. The avatar is managed by `POST /account/image` and `DELETE /account/image`.',
                requestBody: new RequestBody(required: true, content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['name', 'current_password'],
                            'properties' => [
                                'name' => ['type' => 'string'],
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'password' => ['type' => 'string', 'minLength' => 6],
                                'password_confirmation' => ['type' => 'string'],
                                'current_password' => ['type' => 'string', 'minLength' => 6],
                            ],
                        ],
                        'example' => [
                            'name' => 'Example Admin',
                            'email' => 'admin@example.com',
                            'password' => 'new-secret',
                            'password_confirmation' => 'new-secret',
                            'current_password' => 'admin123',
                        ],
                    ],
                ])),
                responses: [
                    '200' => new Response(
                        description: 'The updated profile.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => '1',
                            'name' => 'Example Admin',
                            'email' => 'admin@example.com',
                            'image' => null,
                            'imageUrl' => null,
                            'status' => '1',
                            'roleId' => 1,
                            'roleName' => 'Administrator',
                            'success' => true,
                            'message' => 'Account updated successfully.',
                        ]]]),
                    ),
                    '401' => new Response(description: 'Missing or invalid admin token.'),
                    '422' => new Response(description: 'Validation failed, or the current password is wrong.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/account/image',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: AdminAccountProcessor::class,
            status: 200,
            read: false,
            deserialize: false,
            openapi: new Operation(
                tags: ['Admin Authentication'],
                summary: 'Upload my avatar',
                description: 'Uploads (or replaces) the authenticated admin\'s avatar. Multipart fields: `image` (bmp, jpeg, jpg, png or webp), `current_password`, and an optional `file_name`.',
                requestBody: new RequestBody(required: true, content: new \ArrayObject([
                    'multipart/form-data' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['image', 'current_password'],
                            'properties' => [
                                'image' => ['type' => 'string', 'format' => 'binary'],
                                'current_password' => ['type' => 'string'],
                                'file_name' => ['type' => 'string', 'maxLength' => 150],
                            ],
                        ],
                    ],
                ])),
                responses: [
                    '200' => new Response(
                        description: 'The updated profile.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => '1',
                            'name' => 'Example Admin',
                            'email' => 'admin@example.com',
                            'image' => 'admins/1/avatar.png',
                            'imageUrl' => 'https://example.com/storage/admins/1/avatar.png',
                            'status' => '1',
                            'roleId' => 1,
                            'roleName' => 'Administrator',
                            'success' => true,
                            'message' => 'Account updated successfully.',
                        ]]]),
                    ),
                    '401' => new Response(description: 'Missing or invalid admin token.'),
                    '422' => new Response(description: 'Validation failed, or the current password is wrong.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/account/image',
            processor: AdminAccountProcessor::class,
            status: 200,
            read: false,
            deserialize: false,
            openapi: new Operation(
                tags: ['Admin Authentication'],
                summary: 'Remove my avatar',
                description: 'Removes the authenticated admin\'s avatar. Send `current_password` in the JSON body.',
                requestBody: new RequestBody(required: true, content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['current_password'],
                            'properties' => ['current_password' => ['type' => 'string']],
                        ],
                        'example' => ['current_password' => 'admin123'],
                    ],
                ])),
                responses: [
                    '200' => new Response(description: 'The updated profile, with `image` null.'),
                    '401' => new Response(description: 'Missing or invalid admin token.'),
                    '422' => new Response(description: 'The current password is missing or wrong.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'read',
            resolver: AdminProfileQueryResolver::class,
            args: [],
            normalizationContext: ['groups' => ['query']],
            description: "Read the authenticated admin's profile using the Bearer token in the Authorization header.",
        ),
        new Mutation(
            name: 'update',
            input: AdminAccountUpdateInput::class,
            processor: AdminAccountProcessor::class,
            read: false,
            normalizationContext: ['groups' => ['query']],
            description: "Update the authenticated admin's name, email and password. The `id` input is ignored; currentPassword is required.",
        ),
    ]
)]
class AdminProfile
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $name = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $image = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $image_url = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?int $role_id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $role_name = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $message = null;
}
