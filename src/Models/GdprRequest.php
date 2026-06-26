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
use Webkul\BagistoApi\Dto\CreateGdprRequestInput;
use Webkul\BagistoApi\Dto\DeleteGdprRequestInput;
use Webkul\BagistoApi\Dto\RevokeGdprRequestInput;
use Webkul\BagistoApi\Resolver\GdprRequestQueryResolver;
use Webkul\BagistoApi\State\GdprRequestItemProvider;
use Webkul\BagistoApi\State\GdprRequestProcessor;
use Webkul\BagistoApi\State\GdprRequestProvider;

#[ApiResource(
    shortName: 'GdprRequest',
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/gdpr-requests/{id}',
            provider: GdprRequestItemProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(tags: ['GDPR Requests']),
        ),
        new GetCollection(
            uriTemplate: '/gdpr-requests',
            provider: GdprRequestProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['GDPR Requests'],
                summary: 'List the customer\'s own GDPR data requests',
                parameters: [
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'sort',
                        in: 'query',
                        description: 'Column to sort by: `id` (default) or `created_at`.',
                        required: false,
                        schema: ['type' => 'string', 'enum' => ['id', 'created_at', 'id-asc', 'id-desc', 'created_at-asc', 'created_at-desc']],
                    ),
                    new \ApiPlatform\OpenApi\Model\Parameter(
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
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['GDPR Requests'],
                summary: 'Raise a GDPR data request',
                description: 'Raise a GDPR data request for the authenticated customer. Type must be `delete` or `update`.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    description: 'GDPR data request details',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['type', 'message'],
                                'properties' => [
                                    'type'    => ['type' => 'string', 'enum' => ['delete', 'update'], 'example' => 'delete'],
                                    'message' => ['type' => 'string', 'example' => 'Please delete all my personal data.'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Post(
            name: 'revoke_post',
            uriTemplate: '/gdpr-requests/{id}/revoke',
            status: 200,
            processor: GdprRequestProcessor::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['GDPR Requests'],
                summary: 'Revoke a GDPR data request',
                description: 'Revoke one of the customer\'s own GDPR data requests. Allowed only while the request is pending or processing.',
            ),
        ),
        new Delete(
            uriTemplate: '/gdpr-requests/{id}',
            processor: GdprRequestProcessor::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(tags: ['GDPR Requests']),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: GdprRequestQueryResolver::class),
        new QueryCollection(
            provider: GdprRequestProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'sort'  => ['type' => 'String'],
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
class GdprRequest extends \Webkul\GDPR\Models\GDPRDataRequest
{
    protected $appends = ['success_message'];

    public ?string $responseMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false, description: 'The customer who owns the request')]
    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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
