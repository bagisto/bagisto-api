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

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Enter the name for this storefront key');

        if (empty($name)) {
            $this->error(__('bagistoapi::app.graphql.install.key-name-required'));

            return self::FAILURE;
        }

        if (StorefrontKey::where('name', $name)->exists()) {
            $this->error(__('bagistoapi::app.graphql.install.key-already-exists', ['name' => $name]));

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

        $this->info(__('bagistoapi::app.graphql.install.key-generated-success'));
        $this->newLine();
        $this->line('<info>'.__('bagistoapi::app.graphql.install.key-details').'</info>');
        $this->line("  <fg=cyan>".__('bagistoapi::app.graphql.install.key-field-id')."</> : {$storefront->id}");
        $this->line("  <fg=cyan>".__('bagistoapi::app.graphql.install.key-field-name')."</> : {$storefront->name}");
        $this->line("  <fg=cyan>".__('bagistoapi::app.graphql.install.key-field-key')."</> : <fg=yellow>{$key}</>");
        $this->line("  <fg=cyan>".__('bagistoapi::app.graphql.install.key-field-rate-limit')."</> : {$rateLimit}".__('bagistoapi::app.graphql.install.key-requests-minute'));
        $this->line('  <fg=cyan>'.__('bagistoapi::app.graphql.install.key-field-status').'</> : '.($storefront->is_active ? '<fg=green>Active</>' : '<fg=red>Inactive</>'));
        $this->newLine();
        $this->warn(__('bagistoapi::app.graphql.install.key-secure-warning'));
        $this->warn(__('bagistoapi::app.graphql.install.key-share-warning'));

        return self::SUCCESS;
    }
}
