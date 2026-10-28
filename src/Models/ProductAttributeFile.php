<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\ProductAttributeFileOutput;
use Webkul\BagistoApi\Resolver\ProductAttributeFileQueryResolver;
use Webkul\BagistoApi\State\ProductAttributeFileProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductAttributeFile',
    operations: [
        new Get(
            uriTemplate: '/products/{productId}/attribute-files/{attributeId}',
            requirements: [
                'productId' => '\\d+',
                'attributeId' => '\\d+',
            ],
            provider: ProductAttributeFileProvider::class,
            outputFormats: ['binary' => ['application/octet-stream']],
            openapi: new Model\Operation(
                tags: ['Product'],
                summary: 'Download a product file or image attribute',
                description: 'Downloads a stored file/image EAV attribute for a product visible in the current storefront channel. The GraphQL productAttributeFile query returns the same file base64-encoded.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Storefront-visible product ID.', true, schema: ['type' => 'integer']),
                    new Model\Parameter('attributeId', 'path', 'File or image attribute ID.', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    200 => new Model\Response(description: 'File downloaded.', content: new \ArrayObject(['application/octet-stream' => ['schema' => [
                        'type' => 'string',
                        'format' => 'binary',
                    ]]])),

                    404 => new Model\Response(description: 'Product, file attribute, or stored file not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'item_query',
            resolver: ProductAttributeFileQueryResolver::class,
            output: ProductAttributeFileOutput::class,
            normalizationContext: [
                'groups' => ['query'],
            ],
            args: [
                'productId' => ['type' => 'Int!'],
                'attributeId' => ['type' => 'Int!'],
            ],
            description: 'Return a stored file/image attribute of a storefront-visible product, base64-encoded in `content` along with its file name, mime type, and size.',
        ),
    ],
)]
class ProductAttributeFile {}
