<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearApiPlatformCacheCommand extends Command
{
    /**
     * The signature.
     */
    protected $signature = 'bagisto-api-platform:clear-cache {--store= : Override cache store name to flush}';

    /**
     * The description.
     */
    protected $description = 'Flush API Platform metadata/schema cache store(s) so GraphQL/OpenAPI schema updates are picked up.';

    /**
     * Handle the request.
     */
    public function handle(): int
    {
        $configuredStore = (string) config('api-platform.cache', 'file');
        $schemaCacheEnabled = (bool) config('api-platform.schema_cache.enabled', false);
        $schemaStore = $schemaCacheEnabled
            ? (string) config('api-platform.schema_cache.store', $configuredStore)
            : $configuredStore;

        $overrideStore = $this->option('store');
        if (
            \is_string($overrideStore)
            && $overrideStore !== ''
        ) {
            $configuredStore = $overrideStore;
            $schemaStore = $overrideStore;
        }

        $stores = array_values(array_unique(array_filter([$configuredStore, $schemaStore])));

        foreach ($stores as $store) {
            try {
                Cache::store($store)->flush();
                $this->info(sprintf('Flushed cache store "%s".', $store));
            } catch (\Throwable $exception) {
                $this->error(sprintf('Failed to flush cache store "%s": %s', $store, $exception->getMessage()));

                return self::FAILURE;
            }
        }

        $this->line('If you are running PHP-FPM with OPcache in production, you may also need to restart PHP-FPM.');

        return self::SUCCESS;
    }
}
