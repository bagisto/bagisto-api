<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminConfigurationCacheInput;
use Webkul\BagistoApi\Admin\State\AdminConfigurationCacheProcessor;

/**
 * Runs one of the cache actions from Configuration > Cache Management, mirroring the
 * admin cache-management execute action and its fixed action allow-list.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminConfigurationCache',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/configuration/cache',
            input: AdminConfigurationCacheInput::class,
            processor: AdminConfigurationCacheProcessor::class,
            status: 200,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Admin Configuration'],
                summary: 'Run a cache management action',
                description: 'Runs one cache action, the same set the Cache Management screen offers. Clear actions: `clear-all`, `clear-config`, `clear-cache`, `clear-compiled`, `clear-events`, `clear-routes`, `clear-views`. Build actions: `build-all`, `build-config`, `build-routes`, `build-views`. Any other value returns 422 with `success: false`; a command that fails also returns 422 with its output.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['action'],
                                'properties' => [
                                    'action' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'clear-all', 'clear-config', 'clear-cache', 'clear-compiled', 'clear-events', 'clear-routes', 'clear-views',
                                            'build-all', 'build-config', 'build-routes', 'build-views',
                                        ],
                                    ],
                                ],
                            ],
                            'example' => ['action' => 'clear-views'],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The action ran.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Action "view:clear" executed successfully.',
                                    'output' => 'INFO  Compiled views cleared successfully.',
                                    'command' => 'view:clear',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Missing, unknown or failed action.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                    '403' => new Model\Response(description: 'Missing the configuration permission.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminConfigurationCacheInput::class,
            processor: AdminConfigurationCacheProcessor::class,
            description: 'Run a cache management action. Becomes createAdminConfigurationCache.',
        ),
    ],
)]
class AdminConfigurationCache
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $action = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;

    #[ApiProperty(writable: false, description: 'Console output of the command.')]
    public ?string $output = null;

    #[ApiProperty(writable: false, description: 'The artisan command that ran.')]
    public ?string $command = null;
}
