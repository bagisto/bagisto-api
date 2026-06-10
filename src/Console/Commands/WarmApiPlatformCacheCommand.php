<?php

namespace Webkul\BagistoApi\Console\Commands;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Illuminate\Console\Command;

class WarmApiPlatformCacheCommand extends Command
{
    protected $signature = 'bagisto-api-platform:warm-cache';

    protected $description = 'Pre-build the API Platform resource-metadata cache so the first request after a deploy/cache-clear does not pay the full metadata rebuild cost.';

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
            } catch (\Throwable $e) {
                $failed++;
                $this->warn(sprintf('Skipped "%s": %s', $resourceClass, $e->getMessage()));
            }
        }

        $this->info(sprintf('Warmed metadata cache for %d resource(s)%s.', $count, $failed ? sprintf(' (%d skipped)', $failed) : ''));

        return self::SUCCESS;
    }
}
