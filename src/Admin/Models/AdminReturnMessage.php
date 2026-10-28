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
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA conversation messages, newest first.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 90,
                                        'rmaId' => 12,
                                        'message' => 'We have received your package.',
                                        'isAdmin' => true,
                                        'attachment' => null,
                                        'attachmentUrl' => null,
                                        'createdAt' => '2026-07-20T11:30:00+00:00',
                                    ],
                                    [
                                        'id' => 87,
                                        'rmaId' => 12,
                                        'message' => 'The hoodie zipper is broken.',
                                        'isAdmin' => false,
                                        'attachment' => 'rma/12/messages/zipper.jpg',
                                        'attachmentUrl' => 'https://example.com/storage/rma/12/messages/zipper.jpg',
                                        'createdAt' => '2026-07-20T10:20:00+00:00',
                                    ],
                                ],
                            ],
                        ]),
                    ),
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
                responses: [
                    '201' => new Model\Response(
                        description: 'The created admin message.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 91,
                                    'rmaId' => 12,
                                    'message' => 'We have received your package.',
                                    'isAdmin' => true,
                                    'attachment' => null,
                                    'attachmentUrl' => null,
                                    'createdAt' => '2026-07-20T11:35:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
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
