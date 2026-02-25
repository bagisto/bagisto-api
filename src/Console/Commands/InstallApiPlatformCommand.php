<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InstallApiPlatformCommand extends Command
{
    protected $signature = 'bagisto-api-platform:install';

    protected $description = 'Install and configure API Platform for Bagisto';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Execute the installation process.
     */
    public function handle(): int
    {
        $this->info(__('bagistoapi::app.graphql.install.starting'));

        try {
            $this->publishPackageAssets();
            
            $this->registerServiceProvider();
                     
            $this->linkApiPlatformAssets();

            $this->updateComposerAutoload();

            $this->makeTranslatableModelAbstract();

            $this->registerApiPlatformProviders();

            $this->runDatabaseMigrations();           

            $this->clearAndOptimizeCaches();
            
            $this->generateApiKey();

            $this->publishConfiguration();

            $this->clearAndOptimizeCaches();

            $this->info(__('bagistoapi::app.graphql.install.completed-success'));
            $this->newLine();
            
            $appUrl = config('app.url');

            $this->newLine();
            $this->info(__('bagistoapi::app.graphql.install.api-endpoints'));
            $this->line(__('bagistoapi::app.graphql.install.api-documentation', ['url' => 'https://api-docs.bagisto.com/']));
            $this->line(__('bagistoapi::app.graphql.install.api-landing-page', ['url' => "{$appUrl}/api"]));
            $this->line(__('bagistoapi::app.graphql.install.graphql-playground', ['url' => "{$appUrl}/api/graphiql"]));
            $this->line(__('bagistoapi::app.graphql.install.rest-api-storefront', ['url' => "{$appUrl}/api/shop"]));
            $this->line(__('bagistoapi::app.graphql.install.rest-api-admin', ['url' => "{$appUrl}/api/admin"]));

            $this->newLine();
            $this->info(__('bagistoapi::app.graphql.install.completed-info'));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error(__('bagistoapi::app.graphql.install.failed').$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Register the API Platform service provider.
     */
    protected function registerServiceProvider(): void
    {
        $providersPath = base_path('bootstrap/providers.php');

        if (! $this->files->exists($providersPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.provider-file-not-found', ['file' => $providersPath]));
        }

        if (! is_writable($providersPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.provider-permission-denied', ['file' => $providersPath]));
        }

        $content = $this->files->get($providersPath);

        $providerClass = 'Webkul\\BagistoApi\\Providers\\BagistoApiServiceProvider::class';

        if (strpos($content, $providerClass) !== false) {
            $this->comment(__('bagistoapi::app.graphql.install.provider-already-registered'));

            return;
        }

        $content = preg_replace(
            '/(\],\s*\);)/',
            "    $providerClass,\n$1",
            $content
        );

        $this->files->put($providersPath, $content);

        $this->line(__('bagistoapi::app.graphql.install.provider-registered'));
    }

    /**
     * Publish the API Platform configuration file.
     */
    protected function publishConfiguration(): void
    {
        $source = __DIR__.'/../../../config/api-platform.php';
        $vendorSource = __DIR__.'/../../../config/api-platform-vendor.php';

        $destination = config_path('api-platform.php');

        if ($this->files->exists(base_path('vendor/bagisto/bagisto-api/config/api-platform-vendor.php'))) { 
            $source = $vendorSource;
        }

        if (! $this->files->exists($source)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.config-source-not-found', ['file' => $source]));
        }

        if ($this->files->exists($destination)) {
            $this->comment(__('bagistoapi::app.graphql.install.config-already-exists'));

            return;
        }

        $configDir = dirname($destination);
        if (! is_writable($configDir)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.config-permission-denied', ['directory' => $configDir]));
        }

        $this->files->copy($source, $destination);
        $this->line(__('bagistoapi::app.graphql.install.config-published'));
    }

    /**
     * Publish package assets via vendor:publish command.
     */
    protected function publishPackageAssets(): void
    {
        try {
            $process = new Process([
                'php',
                'artisan',
                'vendor:publish',
                '--provider=Webkul\BagistoApi\Providers\BagistoApiServiceProvider',
                '--no-interaction',
            ]);

            $process->run();

            if (! $process->isSuccessful()) {
                $this->warn(__('bagistoapi::app.graphql.install.publish-assets-warning', ['error' => $process->getErrorOutput()]));

                return;
            }

            $this->line(__('bagistoapi::app.graphql.install.assets-published'));
        } catch (\Exception $e) {
            $this->warn(__('bagistoapi::app.graphql.install.publish-assets-warning', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Update composer.json with required configurations.
     */
    protected function updateComposerAutoload(): void
    {
        $composerPath = base_path('composer.json');

        if (! $this->files->exists($composerPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.composer-file-not-found', ['file' => $composerPath]));
        }

        if (! is_writable($composerPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.composer-permission-denied', ['file' => $composerPath]));
        }

        $composer = json_decode($this->files->get($composerPath), true);

        if (! isset($composer['autoload']['psr-4'])) {
            $composer['autoload']['psr-4'] = [];
        }

        $composer['autoload']['psr-4']['Webkul\\GraphQL\\'] = 'packages/Webkul/GraphQL/src';

        if (! isset($composer['extra']['laravel']['dont-discover'])) {
            $composer['extra']['laravel']['dont-discover'] = [];
        }

        if (! in_array('api-platform/laravel', $composer['extra']['laravel']['dont-discover'])) {
            $composer['extra']['laravel']['dont-discover'][] = 'api-platform/laravel';
        }

        $this->files->put($composerPath, json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL);
        $this->line(__('bagistoapi::app.graphql.install.composer-updated'));
    }

    /**
     * Make TranslatableModel abstract for API Platform compatibility.
     */
    protected function makeTranslatableModelAbstract(): void
    {
        $modelPath = base_path('packages/Webkul/Core/src/Eloquent/TranslatableModel.php');

        if (! $this->files->exists($modelPath)) {
            $this->comment(__('bagistoapi::app.graphql.install.translatable-not-found'));

            return;
        }

        if (! is_writable($modelPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.translatable-permission-denied', ['file' => $modelPath]));
        }

        $content = $this->files->get($modelPath);

        if (preg_match('/abstract\s+class\s+TranslatableModel/', $content)) {
            $this->comment(__('bagistoapi::app.graphql.install.translatable-already-abstract'));

            return;
        }

        $content = preg_replace(
            '/class\s+TranslatableModel/',
            'abstract class TranslatableModel',
            $content
        );

        $this->files->put($modelPath, $content);
        $this->line(__('bagistoapi::app.graphql.install.translatable-made-abstract'));
    }

    /**
     * Register API Platform providers in bootstrap/app.php.
     */
    protected function registerApiPlatformProviders(): void
    {
        $appPath = base_path('bootstrap/app.php');

        if (! $this->files->exists($appPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.providers-file-not-found', ['file' => $appPath]));
        }

        if (! is_writable($appPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.providers-permission-denied', ['file' => $appPath]));
        }

        $content = $this->files->get($appPath);

        if (strpos($content, 'ApiPlatformProvider::class') !== false) {
            $this->comment(__('bagistoapi::app.graphql.install.providers-already-registered'));

            return;
        }

        $providers = "\n->withProviders([\n"
            ."     \\ApiPlatform\\Laravel\\ApiPlatformProvider::class,\n"
            ."     \\ApiPlatform\\Laravel\\ApiPlatformDeferredProvider::class,\n"
            ."     \\ApiPlatform\\Laravel\\Eloquent\\ApiPlatformEventProvider::class,\n"
            ."])\n";

        if (strpos($content, '->create()') !== false) {
            $content = str_replace('->create()', $providers.'->create()', $content);
        } else {
            throw new \Exception(__('bagistoapi::app.graphql.install.providers-not-found'));
        }

        $this->files->put($appPath, $content);
        $this->line(__('bagistoapi::app.graphql.install.providers-registered'));
    }

    /**
     * Link or copy API Platform assets to public directory.
     */
    protected function linkApiPlatformAssets(): void
    {
        $vendorPath = base_path('vendor/api-platform/laravel/public');
        $publicPath = public_path('vendor/api-platform');

        if (! $this->files->exists($vendorPath)) {
            $this->line(__('bagistoapi::app.graphql.install.vendor-path-not-found', ['path' => $vendorPath]));

            return;
        }

        if ($this->files->exists($publicPath)) {
            $this->line(__('bagistoapi::app.graphql.install.assets-already-linked', ['path' => $publicPath]));

            return;
        }

        $publicVendorDir = dirname($publicPath);
        if (! $this->files->exists($publicVendorDir)) {
            $this->files->makeDirectory($publicVendorDir, 0755, true);
        }

        try {
            symlink($vendorPath, $publicPath);
            $this->line(__('bagistoapi::app.graphql.install.asset-linked-success'));
        } catch (\Exception $e) {
            $this->comment(__('bagistoapi::app.graphql.install.symlink-create-failed'));
            if (! $this->files->copyDirectory($vendorPath, $publicPath)) {
                $this->warn(__('bagistoapi::app.graphql.install.asset-copy-warning'));

                return;
            }
            $this->line(__('bagistoapi::app.graphql.install.asset-copied-success'));
        }
    }

    /**
     * Run database migrations.
     */
    protected function runDatabaseMigrations(): void
    {
        try {
            $this->info(__('bagistoapi::app.graphql.install.running-migrations'));

            $process = new Process([
                'php',
                'artisan',
                'migrate',
            ]);

            $process->run();

            if (! $process->isSuccessful()) {
                throw new \Exception(__('bagistoapi::app.graphql.install.migrations-error').' '.$process->getErrorOutput());
            }

            $this->line(__('bagistoapi::app.graphql.install.migrations-completed'));
        } catch (\Exception $e) {
            throw new \Exception(__('bagistoapi::app.graphql.install.migrations-error-running', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Clear and optimize application caches.
     */
    protected function clearAndOptimizeCaches(): void
    {
        try {
            $this->info(__('bagistoapi::app.graphql.install.clearing-caches'));

            $clearProcess = new Process([
                'php',
                'artisan',
                'config:clear',
            ]);

            $clearProcess->run();

            if (! $clearProcess->isSuccessful()) {
                throw new \Exception(__('bagistoapi::app.graphql.install.cache-clear-error').' '.$clearProcess->getErrorOutput());
            }

            $cacheProcess = new Process([
                'php',
                'artisan',
                'cache:clear',
            ]);

            $cacheProcess->run();

            if (! $cacheProcess->isSuccessful()) {
                throw new \Exception(__('bagistoapi::app.graphql.install.cache-clear-error').' '.$cacheProcess->getErrorOutput());
            }

            $clearProcess = new Process([
                'php',
                'artisan',
                'optimize:clear',
            ]);

            $clearProcess->run();

            if (! $clearProcess->isSuccessful()) {
                throw new \Exception(__('bagistoapi::app.graphql.install.cache-clear-error').' '.$clearProcess->getErrorOutput());
            }

            $optimizeProcess = new Process([
                'php',
                'artisan',
                'optimize',
            ]);

            $optimizeProcess->run();

            if (! $optimizeProcess->isSuccessful()) {
                throw new \Exception(__('bagistoapi::app.graphql.install.cache-optimize-error').' '.$optimizeProcess->getErrorOutput());
            }

            $this->line(__('bagistoapi::app.graphql.install.caches-optimized'));
        } catch (\Exception $e) {
            throw new \Exception(__('bagistoapi::app.graphql.install.cache-error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Generate a default storefront API key.
     */
    protected function generateApiKey(): void
    {
        try {
            $this->info(__('bagistoapi::app.graphql.install.generating-api-key'));

            $process = new Process([
                'php',
                'artisan',
                'bagisto-api:generate-key',
                '--name=Default Storefront Key1',
            ]);

            $process->run();

            $output = $process->getOutput().$process->getErrorOutput();

            if (stripos($output, 'already exists') !== false) {
                $this->comment(__('bagistoapi::app.graphql.install.api-key-already-exists'));

                return;
            }

            if (! $process->isSuccessful()) {
                throw new \Exception(__('bagistoapi::app.graphql.install.api-key-generation-error').' '.$process->getErrorOutput());
            }

            $this->line(__('bagistoapi::app.graphql.install.api-key-generated'));

            $generatedKey = $this->extractKeyFromOutput($output);
            $this->saveStorefrontConfigToEnv($generatedKey);
        } catch (\Exception $e) {
            throw new \Exception(__('bagistoapi::app.graphql.install.api-key-error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Extract API key from command output.
     */
    protected function extractKeyFromOutput(string $output): string
    {
        if (preg_match('/\b(pk_[a-zA-Z0-9_]+)\b/', $output, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Save storefront configuration to .env file.
     */
    protected function saveStorefrontConfigToEnv(string $generatedKey = ''): void
    {
        $envPath = base_path('.env');

        if (! $this->files->exists($envPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.env-file-not-found'));
        }

        if (! is_writable($envPath)) {
            throw new \Exception(__('bagistoapi::app.graphql.install.env-permission-denied'));
        }

        $envContent = $this->files->get($envPath);

        $envVariables = [
            'STOREFRONT_DEFAULT_RATE_LIMIT' => '100',
            'STOREFRONT_CACHE_TTL' => '60',
            'STOREFRONT_KEY_PREFIX' => 'storefront_key_',
            'STOREFRONT_PLAYGROUND_KEY' => $generatedKey,
            'API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY' => 'false',
        ];

        foreach ($envVariables as $key => $value) {
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        $this->files->put($envPath, $envContent);

        $this->line(__('bagistoapi::app.graphql.install.env-config-saved'));
    }
}
