<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;

class OptimizeApiPlatformCommand extends Command
{
    protected $signature = 'bagisto-api-platform:optimize';

    protected $description = 'Full deploy optimization for the API: clears stale caches, then rebuilds the config + route caches and pre-warms the API Platform metadata cache. Run this after every deploy, package update, or endpoint change so no request pays the per-request route rebuild or the cold-start metadata build.';

    public function handle(): int
    {
        $this->components->info('Optimizing the Bagisto API (full optimize + metadata caches)...');

        $this->call('optimize:clear');
        $this->call('bagisto-api-platform:clear-cache');
        $this->call('optimize');

        if (! app()->routesAreCached()) {
            $this->components->error('The route cache was not built. Without it, API Platform re-registers every route on every request (~0.8s slower per call). Check for a route that cannot be serialized (a closure or a duplicate route name), then re-run this command before deploying.');

            return self::FAILURE;
        }

        $this->call('bagisto-api-platform:warm-cache');

        $this->components->info('Bagisto API optimized. Config, events, routes and views are cached and the metadata cache is warm.');

        if (config('app.debug')) {
            $this->newLine();
            $this->components->warn('APP_DEBUG is currently true. For faster responses set APP_DEBUG=false in your .env (debug mode adds error-collector overhead to every request), then re-run this command.');
        }

        return self::SUCCESS;
    }
}
