<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminReturnFileProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReturnFile',
    operations: [
        new Get(
            uriTemplate: '/rma/requests/images/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminReturnFileProvider::class,
            outputFormats: ['binary' => ['application/octet-stream']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Show an image attached to an RMA request',
                description: 'Streams one image of a return request for display (inline, with the stored file\'s content type), as the admin panel does. The id is an `images[].id` from `GET /api/admin/rma/requests/{id}`. Send `Accept: application/octet-stream`. Binary response, not JSON. Permission: sales.rma.requests.',
                parameters: [
                    new Model\Parameter('id', 'path', 'RMA image id.', true, schema: [
                        'type' => 'integer',
                        'example' => 5,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The image is streamed inline.',
                        content: new \ArrayObject([
                            'application/octet-stream' => [
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'binary',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.rma.requests.'),
                    '404' => new Model\Response(description: 'Image or stored file not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/rma/messages/{id}/attachment',
            requirements: ['id' => '\d+'],
            provider: AdminReturnFileProvider::class,
            outputFormats: ['binary' => ['application/octet-stream']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Download the attachment of an RMA message',
                description: 'Streams the file attached to one RMA conversation message as a download under its original name, as the admin panel does. The id is a message id from `GET /api/admin/rma/messages`. Send `Accept: application/octet-stream`. Binary response, not JSON. Permission: sales.rma.requests.',
                parameters: [
                    new Model\Parameter('id', 'path', 'RMA message id.', true, schema: [
                        'type' => 'integer',
                        'example' => 87,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The attachment is downloaded.',
                        content: new \ArrayObject([
                            'application/octet-stream' => [
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'binary',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.rma.requests.'),
                    '404' => new Model\Response(description: 'Message, attachment or stored file not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class AdminReturnFile
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
