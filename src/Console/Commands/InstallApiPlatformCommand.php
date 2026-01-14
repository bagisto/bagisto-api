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

    public function handle(): int
    {
        $this->info(__('bagistoapi::app.graphql.install.starting'));

        try {
            $this->registerServiceProvider();

            $this->publishConfiguration();

            $this->publishPackageAssets();

            $this->linkApiPlatformAssets();

            $this->updateComposerAutoload();

            $this->makeTranslatableModelAbstract();

            $this->registerApiPlatformProviders();

            $this->runDatabaseMigrations();

            $this->clearAndOptimizeCaches();

            $this->generateApiKey();

            $this->info(__('bagistoapi::app.graphql.install.completed-success'));
            $this->newLine();
            $this->info(__('bagistoapi::app.graphql.install.completed-info'));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error(__('bagistoapi::app.graphql.install.failed').$e->getMessage());

            return self::FAILURE;
        }
    }

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

    protected function publishConfiguration(): void
    {
        $source = __DIR__.'/../../../config/api-platform.php';
        $destination = config_path('api-platform.php');

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
                $this->warn('Warning: Could not publish package assets. '.PHP_EOL.$process->getErrorOutput());

                return;
            }

            $this->line(__('bagistoapi::app.graphql.install.assets-published'));
        } catch (\Exception $e) {
            $this->warn('Warning: Could not publish package assets. '.$e->getMessage());
        }
    }

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

        $providers = "        ->withProviders([\n"
            ."            \\ApiPlatform\\Laravel\\ApiPlatformProvider::class,\n"
            ."            \\ApiPlatform\\Laravel\\ApiPlatformDeferredProvider::class,\n"
            ."            \\ApiPlatform\\Laravel\\Eloquent\\ApiPlatformEventProvider::class,\n"
            ."        ])\n";

        if (strpos($content, '->create()') !== false) {
            $content = str_replace('->create()', $providers.'->create()', $content);
        } else {
            throw new \Exception(__('bagistoapi::app.graphql.install.providers-not-found'));
        }

        $this->files->put($appPath, $content);
        $this->line(__('bagistoapi::app.graphql.install.providers-registered'));
    }

    protected function linkApiPlatformAssets(): void
    {
        $vendorPath = base_path('vendor/api-platform/laravel/public');
        $publicPath = public_path('vendor/api-platform');

        if (! $this->files->exists($vendorPath)) {
            $this->line('API Platform vendor path not found at: '.$vendorPath);

            return;
        }

        if ($this->files->exists($publicPath)) {
            $this->line('API Platform assets already linked at: '.$publicPath);

            return;
        }

        $publicVendorDir = dirname($publicPath);
        if (! $this->files->exists($publicVendorDir)) {
            $this->files->makeDirectory($publicVendorDir, 0755, true);
        }

        try {
            // Use absolute path for symlink
            symlink($vendorPath, $publicPath);
            $this->line('✓ API Platform assets linked successfully');
        } catch (\Exception $e) {
            $this->comment('Could not create symlink, copying assets instead...');
            // Fallback: copy instead of symlink
            if (! $this->files->copyDirectory($vendorPath, $publicPath)) {
                $this->warn('Warning: Could not link or copy API Platform assets. Manual setup may be required.');

                return;
            }
            $this->line('✓ API Platform assets copied successfully');
        }
    }

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
                throw new \Exception('Database migrations failed. '.$process->getErrorOutput());
            }

            $this->line(__('bagistoapi::app.graphql.install.migrations-completed'));
        } catch (\Exception $e) {
            throw new \Exception('Error running database migrations: '.$e->getMessage());
        }
    }

    protected function clearAndOptimizeCaches(): void
    {
        try {
            $this->info(__('bagistoapi::app.graphql.install.clearing-caches'));

            // Clear caches
            $clearProcess = new Process([
                'php',
                'artisan',
                'optimize:clear',
            ]);

            $clearProcess->run();

            if (! $clearProcess->isSuccessful()) {
                throw new \Exception('Cache clearing failed. '.$clearProcess->getErrorOutput());
            }

            // Optimize
            $optimizeProcess = new Process([
                'php',
                'artisan',
                'optimize',
            ]);

            $optimizeProcess->run();

            if (! $optimizeProcess->isSuccessful()) {
                throw new \Exception('Optimization failed. '.$optimizeProcess->getErrorOutput());
            }

            $this->line(__('bagistoapi::app.graphql.install.caches-optimized'));
        } catch (\Exception $e) {
            throw new \Exception('Error clearing and optimizing caches: '.$e->getMessage());
        }
    }

    protected function generateApiKey(): void
    {
        try {
            $this->info(__('bagistoapi::app.graphql.install.generating-api-key'));

            $process = new Process([
                'php',
                'artisan',
                'bagisto-api:generate-key',
                '--name=Default Store',
            ]);

            $process->run();

            if (! $process->isSuccessful()) {
                throw new \Exception('API key generation failed. '.$process->getErrorOutput());
            }

            $this->line(__('bagistoapi::app.graphql.install.api-key-generated'));
        } catch (\Exception $e) {
            throw new \Exception('Error generating API key: '.$e->getMessage());
        }
    }
}
