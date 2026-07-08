<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\RequestBody;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\SendCustomerReturnMessageInput;
use Webkul\BagistoApi\State\CustomerReturnMessageProcessor;
use Webkul\BagistoApi\State\CustomerReturnMessageProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerReturnMessage',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/return-messages',
            provider: CustomerReturnMessageProvider::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'List the conversation messages of a return request',
                description: 'Messages on the RMA named by `?return_id=`, newest first. Requires the RMA to belong to the authenticated customer.',
                parameters: [
                    new Parameter('return_id', 'query', 'Return (RMA) id', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/return-messages',
            processor: CustomerReturnMessageProcessor::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'Send a message on a return request',
                description: 'Adds a customer message to the RMA conversation. Body `{ return_id, message }`; an optional file can be attached via multipart `file` (REST only). Returns the created message.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['return_id', 'message'],
                                'properties' => [
                                    'return_id' => ['type' => 'integer', 'example' => 12],
                                    'message' => ['type' => 'string', 'example' => 'Any update on my return?'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CustomerReturnMessageProvider::class,
            paginationType: 'cursor',
            args: [
                'returnId' => ['type' => 'Int!', 'description' => 'Return (RMA) id'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: SendCustomerReturnMessageInput::class,
            processor: CustomerReturnMessageProcessor::class,
        ),
    ],
)]
class CustomerReturnMessage implements SnakeCaseFieldsResource
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $rma_id = null;

    public ?string $message = null;

    public ?bool $is_admin = null;

    public ?string $attachment = null;

    public ?string $attachment_url = null;

    public ?string $created_at = null;

    public static function fromModel($message): self
    {
        $m = new self;
        $m->id = (int) $message->id;
        $m->rma_id = $message->rma_id !== null ? (int) $message->rma_id : null;
        $m->message = $message->message;
        $m->is_admin = (bool) $message->is_admin;
        $m->attachment = $message->attachment;
        $m->attachment_url = $message->attachment_path
            ? Storage::url($message->attachment_path)
            : null;
        $m->created_at = $message->created_at?->toIso8601String();

        return $m;
    }
}
