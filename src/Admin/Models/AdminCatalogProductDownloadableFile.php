<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDownloadableFileProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDownloadableFileProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductDownloadableFile',
    normalizationContext: ['skip_null_values' => false],
    graphQlOperations: [],
    operations: [
        new Post(
            uriTemplate: '/catalog/products/{productId}/downloadable-links/upload',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: AdminCatalogProductDownloadableFileProcessor::class,
            status: 201,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Upload a downloadable link file',
                description: 'Uploads the binary file for a file-type downloadable link and returns its stored path. Send as multipart/form-data with `file`. The returned `path` is what you then set on the link via the product update (`downloadable_links[link_x][file] = path`, `type = file`). URL-type links do not need this — set their `url` directly on update.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['file'],
                                'properties' => [
                                    'file' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'File stored. Use `path` on the product update.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type' => 'link',
                                    'path' => 'product_downloadable_links/12/abc123.zip',
                                    'name' => 'user-manual.zip',
                                    'url' => '/storage/product_downloadable_links/12/abc123.zip',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Missing or invalid file.'),
                    '404' => new Model\Response(description: 'Product not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/catalog/products/{productId}/downloadable-samples/upload',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: AdminCatalogProductDownloadableFileProcessor::class,
            status: 201,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Upload a downloadable sample file',
                description: 'Uploads the binary file for a file-type downloadable sample and returns its stored path. Send as multipart/form-data with `file`. The returned `path` is what you then set on the sample via the product update (`downloadable_samples[sample_x][file] = path`, `type = file`).',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['file'],
                                'properties' => [
                                    'file' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'File stored. Use `path` on the product update.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type' => 'sample',
                                    'path' => 'product_downloadable_samples/12/def456.zip',
                                    'name' => 'preview.zip',
                                    'url' => '/storage/product_downloadable_samples/12/def456.zip',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Missing or invalid file.'),
                    '404' => new Model\Response(description: 'Product not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/catalog/products/{productId}/downloadable/{attributeId}/download',
            requirements: ['productId' => '\d+', 'attributeId' => '\d+'],
            provider: AdminCatalogProductDownloadableFileProvider::class,
            outputFormats: ['binary' => ['application/octet-stream']],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Download a stored downloadable file',
                description: 'Streams the stored file for the given product attribute (a downloadable product\'s file/sample attribute) as a binary attachment. Send `Accept: application/octet-stream`. Binary download, not JSON.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                    new Model\Parameter('attributeId', 'path', 'Attribute ID whose stored file path should be streamed.', true, schema: ['type' => 'integer', 'example' => 26]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'File streamed (application/octet-stream attachment).', content: new \ArrayObject(['application/octet-stream' => ['schema' => ['type' => 'string', 'format' => 'binary']]])),
                    '404' => new Model\Response(description: 'No stored file for the product / attribute.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks catalog.products.'),
                ],
            ),
        ),
    ],
)]
class AdminCatalogProductDownloadableFile
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $path = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $url = null;
}
