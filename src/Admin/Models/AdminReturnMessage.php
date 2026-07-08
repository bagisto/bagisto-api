<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\SendAdminReturnMessageInput;
use Webkul\BagistoApi\Admin\State\AdminReturnMessageProcessor;
use Webkul\BagistoApi\Admin\State\AdminReturnMessageProvider;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReturnMessage',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/messages',
            provider: AdminReturnMessageProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List the conversation messages of an RMA request',
                description: 'Messages on the RMA named by `?return_id=`, newest first.',
                parameters: [
                    new Model\Parameter('return_id', 'query', 'Return (RMA) id', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/rma/messages',
            processor: AdminReturnMessageProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Send an admin message on an RMA request',
                description: 'Adds an admin message to the RMA conversation and notifies the customer. Body `{ return_id, message }`; optional multipart `file` (REST only). Permission: sales.rma.requests.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['return_id', 'message'],
                                'properties' => [
                                    'return_id' => ['type' => 'integer', 'example' => 12],
                                    'message' => ['type' => 'string', 'example' => 'We have received your package.'],
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
            provider: AdminReturnMessageProvider::class,
            paginationType: 'cursor',
            args: [
                'returnId' => ['type' => 'Int!', 'description' => 'Return (RMA) id'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: SendAdminReturnMessageInput::class,
            processor: AdminReturnMessageProcessor::class,
        ),
    ],
)]
class AdminReturnMessage implements SnakeCaseFieldsResource
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
