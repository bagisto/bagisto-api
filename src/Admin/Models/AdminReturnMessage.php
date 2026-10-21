<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
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
                description: 'Messages on the RMA named by `?return_id=`, newest first. `attachment` is the original file name and `attachmentUrl` points at `GET /api/admin/rma/messages/{id}/attachment`, which needs the admin token.',
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
                                    ], [
                                        'id' => 87,
                                        'rmaId' => 12,
                                        'message' => 'The hoodie zipper is broken.',
                                        'isAdmin' => false,
                                        'attachment' => 'zipper.jpg',
                                        'attachmentUrl' => 'https://example.com/api/admin/rma/messages/87/attachment',
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
            inputFormats: [
                'json' => ['application/json'],
                'multipart' => ['multipart/form-data'],
            ],
            deserialize: false,
            processor: AdminReturnMessageProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Send an admin message on an RMA request',
                description: 'Adds an admin message to the RMA conversation and notifies the customer. Body `{ return_id, message }`; optional multipart `file` (REST only), stored on the private disk under `rmas/{return_id}/conversations/{message_id}`. Permission: sales.rma.requests.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['return_id', 'message'],

                                'properties' => [
                                    'return_id' => [
                                        'type' => 'integer',
                                        'example' => 12,
                                    ],

                                    'message' => [
                                        'type' => 'string',
                                        'example' => 'We have received your package.',
                                    ],
                                ],
                            ],
                        ],

                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['return_id', 'message'],

                                'properties' => [
                                    'return_id' => [
                                        'type' => 'integer',
                                        'example' => 12,
                                    ],

                                    'message' => [
                                        'type' => 'string',
                                        'example' => 'We have received your package.',
                                    ],

                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                        'description' => 'Attachment for this message.',
                                    ],
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
                'returnId' => [
                    'type' => 'Int!',
                    'description' => 'Return (RMA) id',
                ],
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
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The rma id.
     */
    public ?int $rma_id = null;

    /**
     * The message.
     */
    public ?string $message = null;

    /**
     * The is admin.
     */
    public ?bool $is_admin = null;

    /**
     * The attachment.
     */
    public ?string $attachment = null;

    /**
     * The attachment url.
     */
    public ?string $attachment_url = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * From model.
     */
    public static function fromModel($message): self
    {
        $result = new self;
        $result->id = (int) $message->id;
        $result->rma_id = $message->rma_id !== null ? (int) $message->rma_id : null;
        $result->message = $message->message;
        $result->is_admin = (bool) $message->is_admin;
        $result->attachment = $message->attachment;
        $result->attachment_url = $message->attachment_path
            ? url('/api/admin/rma/messages/'.$result->id.'/attachment')
            : null;
        $result->created_at = $message->created_at?->toIso8601String();

        return $result;
    }
}
