<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Dto\CreateGdprRequestInput;
use Webkul\BagistoApi\Dto\DeleteGdprRequestInput;
use Webkul\BagistoApi\Dto\RevokeGdprRequestInput;
use Webkul\BagistoApi\Resolver\GdprRequestQueryResolver;
use Webkul\BagistoApi\State\GdprRequestItemProvider;
use Webkul\BagistoApi\State\GdprRequestProcessor;
use Webkul\BagistoApi\State\GdprRequestProvider;
use Webkul\GDPR\Models\GDPRDataRequest;

#[ApiResource(
    shortName: 'GdprRequest',
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/gdpr-requests/{id}',
            provider: GdprRequestItemProvider::class,
            openapi: new Operation(
                tags: ['GDPR Requests'],
                summary: 'Get one of the customer\'s own GDPR data requests',
                description: 'Returns a GDPR data request owned by the authenticated customer. Returns 404 if it is not theirs. Requires GDPR to be enabled in admin config.',
                responses: [
                    '200' => new Response(
                        description: 'The GDPR data request.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 35,
                                    'email' => 'jane@example.com',
                                    'status' => 'pending',
                                    'type' => 'delete',
                                    'message' => 'Please delete my personal data.',
                                    'revokedAt' => null,
                                    'createdAt' => '2026-07-02T12:01:36+05:30',
                                    'updatedAt' => '2026-07-02T12:01:36+05:30',
                                    'successMessage' => null,
                                    'customer' => '/api/shop/customers/1534',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Request not found or not owned by the caller.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/gdpr-requests',
            provider: GdprRequestProvider::class,
            openapi: new Operation(
                tags: ['GDPR Requests'],
                summary: 'List the customer\'s own GDPR data requests',
                description: 'Returns every GDPR data request raised by the authenticated customer. Requires GDPR to be enabled in admin config.',
                responses: [
                    '200' => new Response(
                        description: 'List of the customer\'s GDPR data requests.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 35,
                                        'email' => 'jane@example.com',
                                        'status' => 'pending',
                                        'type' => 'delete',
                                        'message' => 'Please delete my personal data.',
                                        'revokedAt' => null,
                                        'createdAt' => '2026-07-02T12:01:36+05:30',
                                        'updatedAt' => '2026-07-02T12:01:36+05:30',
                                        'successMessage' => null,
                                        'customer' => '/api/shop/customers/1534',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
                parameters: [
                    new Parameter(
                        name: 'sort',
                        in: 'query',
                        description: 'Column to sort by: `id` (default) or `created_at`.',
                        required: false,
                        schema: ['type' => 'string', 'enum' => ['id', 'created_at', 'id-asc', 'id-desc', 'created_at-asc', 'created_at-desc']],
                    ),
                    new Parameter(
                        name: 'order',
                        in: 'query',
                        description: 'Sort direction: `asc` (default) or `desc`.',
                        required: false,
                        schema: ['type' => 'string', 'enum' => ['asc', 'desc']],
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/gdpr-requests',
            processor: GdprRequestProcessor::class,
            openapi: new Operation(
                tags: ['GDPR Requests'],
                summary: 'Raise a GDPR data request',
                description: 'Raise a GDPR data request for the authenticated customer. Type must be `delete` or `update`.',
                requestBody: new RequestBody(
                    description: 'GDPR data request details',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['type', 'message'],
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => ['delete', 'update'], 'example' => 'delete'],
                                    'message' => ['type' => 'string', 'example' => 'Please delete all my personal data.'],
                                ],
                            ],
                            'example' => [
                                'type' => 'delete',
                                'message' => 'Please delete my personal data.',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Request raised. Starts in `pending` status.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 35,
                                    'email' => 'jane@example.com',
                                    'status' => 'pending',
                                    'type' => 'delete',
                                    'message' => 'Please delete my personal data.',
                                    'revokedAt' => null,
                                    'createdAt' => '2026-07-02T12:01:36+05:30',
                                    'updatedAt' => '2026-07-02T12:01:36+05:30',
                                    'successMessage' => 'Your GDPR data request has been raised successfully.',
                                    'customer' => '/api/shop/customers/1534',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Response(description: 'GDPR disabled in admin config, or invalid type/message.'),
                ],
            ),
        ),
        new Post(
            name: 'revoke_post',
            uriTemplate: '/gdpr-requests/{id}/revoke',
            status: 200,
            processor: GdprRequestProcessor::class,
            openapi: new Operation(
                tags: ['GDPR Requests'],
                summary: 'Revoke a GDPR data request',
                description: 'Revoke one of the customer\'s own GDPR data requests. Allowed only while the request is pending or processing. Send an empty JSON body `{}`.',
                responses: [
                    '200' => new Response(
                        description: 'Request revoked. `status` becomes `revoked` and `revokedAt` is stamped.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 35,
                                    'email' => 'jane@example.com',
                                    'status' => 'revoked',
                                    'type' => 'delete',
                                    'message' => 'Please delete my personal data.',
                                    'revokedAt' => '2026-07-02 12:01:37',
                                    'createdAt' => '2026-07-02T12:01:36+05:30',
                                    'updatedAt' => '2026-07-02T12:01:37+05:30',
                                    'successMessage' => null,
                                    'customer' => '/api/shop/customers/1534',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Request is not pending/processing, so it cannot be revoked.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/gdpr-requests/{id}',
            processor: GdprRequestProcessor::class,
            openapi: new Operation(
                tags: ['GDPR Requests'],
                summary: 'Delete a GDPR data request',
                description: 'Deletes one of the customer\'s own GDPR data requests. Returns 204 No Content on success.',
                responses: [
                    '204' => new Response(description: 'Request deleted. No content.'),
                    '404' => new Response(description: 'Request not found or not owned by the caller.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: GdprRequestQueryResolver::class),
        new QueryCollection(
            provider: GdprRequestProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: CreateGdprRequestInput::class,
            output: GdprRequest::class,
            processor: GdprRequestProcessor::class,
        ),
        new Mutation(
            name: 'revoke',
            input: RevokeGdprRequestInput::class,
            output: GdprRequest::class,
            processor: GdprRequestProcessor::class,
        ),
        new Mutation(
            name: 'delete',
            input: DeleteGdprRequestInput::class,
            output: GdprRequest::class,
            processor: GdprRequestProcessor::class,
        ),
    ],
)]
class GdprRequest extends GDPRDataRequest
{
    protected $appends = ['success_message'];

    public ?string $responseMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false, description: 'The customer who owns the request')]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getSuccessMessageAttribute(): ?string
    {
        return $this->responseMessage;
    }

    public function setResponseMessage(string $message): self
    {
        $this->responseMessage = $message;

        return $this;
    }
}
