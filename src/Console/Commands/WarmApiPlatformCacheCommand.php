<?php

namespace Webkul\BagistoApi\Console\Commands;

use ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface;
use ApiPlatform\GraphQl\Type\TypesContainerInterface;
use ApiPlatform\GraphQl\Type\TypesFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Illuminate\Console\Command;
use Webkul\BagistoApi\GraphQl\QueryScopedSchemaBuilder;

class WarmApiPlatformCacheCommand extends Command
{
    /**
     * The signature.
     */
    protected $signature = 'bagisto-api-platform:warm-cache';

    /**
     * The description.
     */
    protected $description = 'Pre-build the API Platform resource-metadata cache so the first request after a deploy/cache-clear does not pay the full metadata rebuild cost.';

    /**
     * Handle the request.
     */
    public function handle(): int
    {
        $nameFactory = app(ResourceNameCollectionFactoryInterface::class);
        $metadataFactory = app(ResourceMetadataCollectionFactoryInterface::class);

        $count = 0;
        $failed = 0;

        foreach ($nameFactory->create() as $resourceClass) {
            try {
                $metadataFactory->create($resourceClass);
                $count++;
            } catch (\Throwable $exception) {
                $failed++;
                $this->warn(sprintf('Skipped "%s": %s', $resourceClass, $exception->getMessage()));
            }
        }

        $this->info(sprintf('Warmed metadata cache for %d resource(s)%s.', $count, $failed ? sprintf(' (%d skipped)', $failed) : ''));

        try {
            $scopedBuilder = new QueryScopedSchemaBuilder(
                $nameFactory,
                $metadataFactory,
                app(TypesFactoryInterface::class),
                app(TypesContainerInterface::class),
                app(FieldsBuilderEnumInterface::class),
            );

            $this->info(sprintf('Pre-built the GraphQL query-scope map (%d fields).', $scopedBuilder->warmFieldMap()));
        } catch (\Throwable $exception) {
            $this->warn(sprintf('Could not pre-build the GraphQL query-scope map: %s', $exception->getMessage()));
        }

        return self::SUCCESS;
    }
}
