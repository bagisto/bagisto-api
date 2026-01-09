<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\BagistoApi\Models\StorefrontKey;

/**
 * Generate a new storefront API key for shop/storefront API authentication
 */
class GenerateStorefrontKey extends Command
{
    protected $signature = 'bagisto-api:generate-key
                            {--name= : Name of the storefront key}
                            {--rate-limit=100 : Rate limit (requests per minute)}
                            {--no-activation : Create the key in inactive state}';

    protected $description = 'Generate a new storefront API key for shop/storefront APIs';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Enter the name for this storefront key');

        if (empty($name)) {
            $this->error('Storefront key name cannot be empty.');

            return self::FAILURE;
        }

        if (StorefrontKey::where('name', $name)->exists()) {
            $this->error("A storefront key with name '{$name}' already exists.");

            return self::FAILURE;
        }

        $rateLimit = (int) $this->option('rate-limit');
        $key = StorefrontKey::generateKey();
        $storefront = StorefrontKey::create([
            'name'       => $name,
            'key'        => $key,
            'is_active'  => ! $this->option('no-activation'),
            'rate_limit' => $rateLimit,
        ]);

        $this->info('Storefront key generated successfully!');
        $this->newLine();
        $this->line('<info>Key Details:</info>');
        $this->line("  <fg=cyan>ID</> : {$storefront->id}");
        $this->line("  <fg=cyan>Name</> : {$storefront->name}");
        $this->line("  <fg=cyan>Key</> : <fg=yellow>{$key}</>");
        $this->line("  <fg=cyan>Rate Limit</> : {$rateLimit} requests/minute");
        $this->line('  <fg=cyan>Status</> : '.($storefront->is_active ? '<fg=green>Active</>' : '<fg=red>Inactive</>'));
        $this->newLine();
        $this->warn('Keep this key secure! It will be used in X-STOREFRONT-KEY header.');
        $this->warn('Do not share this key publicly or commit it to version control.');

        return self::SUCCESS;
    }
}
