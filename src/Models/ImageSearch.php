<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\ImageSearchInput;
use Webkul\BagistoApi\State\ImageSearchProcessor;

/**
 * Search by image: stores the image and, with Magic AI image search on, returns keywords to search with.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ImageSearch',
    operations: [
        new Post(
            uriTemplate: '/image-searches',
            inputFormats: [
                'json' => ['application/json'],
                'multipart' => ['multipart/form-data'],
            ],
            output: self::class,
            processor: ImageSearchProcessor::class,
            read: false,
            deserialize: false,
            validate: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Operation(
                tags: ['Product'],
                summary: 'Search by image',
                description: 'Uploads an image for product search, as the storefront camera search does (jpeg, png, jpg, gif, svg or webp, max 2 MB). Send it as a multipart `image` file, or as a base64 data URI in a JSON `image`. With Magic AI image search switched on, `keywords` holds search keywords and `engine` is `ai`; otherwise `keywords` is empty and `engine` is `tensorflow`, meaning the client derives the keywords from `imageUrl` itself, as the storefront does in the browser. Then search products with the keywords.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['image'],
                                'properties' => [
                                    'image' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['image'],
                                'properties' => [
                                    'image' => ['type' => 'string', 'example' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ...'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'The stored image and the keywords.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'imageUrl' => 'http://localhost/storage/product-search/k3Jd9sQ.jpg',
                                    'keywords' => 'red cotton t-shirt, casual wear, crew neck',
                                    'engine' => 'ai',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Missing image, or not an allowed image type or size.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: ImageSearchInput::class,
            output: self::class,
            processor: ImageSearchProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            description: 'Search by image: send the image as a base64 data URI.',
        ),
    ],
)]
class ImageSearch implements SnakeCaseFieldsResource
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $image_url = null;

    #[ApiProperty(writable: false)]
    public ?string $keywords = null;

    #[ApiProperty(writable: false, schema: ['type' => 'string', 'enum' => ['ai', 'tensorflow']])]
    public ?string $engine = null;
}
