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
use ApiPlatform\OpenApi\Model\Response;
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
                responses: [
                    '200' => new Response(
                        description: 'The RMA conversation messages, newest first.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 88,
                                        'rmaId' => 12,
                                        'message' => 'We have received your request and will inspect the item.',
                                        'isAdmin' => true,
                                        'attachment' => null,
                                        'attachmentUrl' => null,
                                        'createdAt' => '2026-07-20T10:40:00+00:00',
                                    ], [
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
            uriTemplate: '/return-messages',
            inputFormats: [
                'json' => ['application/json'],
                'multipart' => ['multipart/form-data'],
            ],
            deserialize: false,
            processor: CustomerReturnMessageProcessor::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'Send a message on a return request',
                description: 'Sends a message on a return. Attach a file in `file`.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
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
                                        'example' => 'Photo of the damaged zipper attached.',
                                    ],

                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                        'description' => 'Attachment for this message.',
                                    ],
                                ],
                            ],
                        ],

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
                                        'example' => 'Any update on my return?',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'The created message.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 89,
                                    'rmaId' => 12,
                                    'message' => 'Any update on my return?',
                                    'isAdmin' => false,
                                    'attachment' => null,
                                    'attachmentUrl' => null,
                                    'createdAt' => '2026-07-20T11:15:00+00:00',
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
            provider: CustomerReturnMessageProvider::class,
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
            input: SendCustomerReturnMessageInput::class,
            processor: CustomerReturnMessageProcessor::class,
        ),
    ],
)]
class CustomerReturnMessage implements SnakeCaseFieldsResource
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
            ? Storage::url($message->attachment_path)
            : null;
        $result->created_at = $message->created_at?->toIso8601String();

        return $result;
    }
}
