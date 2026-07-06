<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Support\CartOptionFileStaging;

/**
 * Deletes staged customizable-option uploads older than the configured TTL.
 * These are files uploaded but never added to a cart. Schedule it (or run
 * manually) to keep the staging directory from growing.
 */
class PruneCartUploadsCommand extends Command
{
    protected $signature = 'bagisto-api:prune-cart-uploads';

    protected $description = 'Delete abandoned customizable-option uploads older than the TTL.';

    public function handle(CartOptionFileStaging $staging): int
    {
        $config = $staging->config();

        $disk = Storage::disk($config['disk']);

        $cutoff = now()->subMinutes((int) $config['ttl_minutes'])->getTimestamp();

        $deleted = 0;

        foreach ($disk->files($config['stage_dir']) as $file) {
            if ($disk->lastModified($file) < $cutoff) {
                $disk->delete($file);

                $deleted++;
            }
        }

        $this->info("Pruned {$deleted} staged upload(s).");

        return self::SUCCESS;
    }
}
