<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\ImageSearchInput;
use Webkul\BagistoApi\Dto\ImageSearchOutput;
use Webkul\BagistoApi\State\ImageSearchProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ImageSearch',
    operations: [
        new Post(
            uriTemplate: '/image-searches',
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: ImageSearchOutput::class,
            processor: ImageSearchProcessor::class,
            status: 200,
            deserialize: false,
            read: false,
            validate: false,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Model\Operation(
                tags: ['Search'],
                summary: 'Search products by image',
                description: 'Uploads an image and returns its stored URL plus search keywords. When storefront image-search AI is enabled, keywords are analyzed by Magic AI; otherwise the result uses the TensorFlow search engine. Upload the image as a multipart file; the GraphQL mutation accepts the same image base64-encoded.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['image'],

                                'properties' => [
                                    'image' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    200 => new Model\Response(description: 'Image URL, search keywords, and selected engine.'),
                    403 => new Model\Response(description: 'Image search is disabled.'),
                    422 => new Model\Response(description: 'Image is missing, unsupported, or larger than 2 MB.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: ImageSearchInput::class,
            output: ImageSearchOutput::class,
            processor: ImageSearchProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Search products by image. Send the image base64-encoded (raw or a data: URI) in `image`; keywords are analyzed by Magic AI when storefront image search is enabled, otherwise the TensorFlow engine is reported.',
        ),
    ],
)]
class ImageSearch
{
    /**
     * The image url.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $imageUrl = null;

    /**
     * The keywords.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public string $keywords = '';

    /**
     * The engine.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public string $engine = 'tensorflow';
}
