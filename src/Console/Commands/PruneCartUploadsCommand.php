<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Support\CartOptionFileStaging;

class PruneCartUploadsCommand extends Command
{
    /**
     * The signature.
     */
    protected $signature = 'bagisto-api:prune-cart-uploads';

    /**
     * The description.
     */
    protected $description = 'Delete abandoned customizable-option uploads older than the TTL.';

    /**
     * Handle the request.
     */
    public function handle(CartOptionFileStaging $staging): int
    {
        $disk = Storage::disk(CartOptionFileStaging::DISK);

        $cutoff = now()->subMinutes($staging->ttlMinutes())->getTimestamp();

        $deleted = 0;

        foreach ($disk->files(CartOptionFileStaging::STAGE_DIR) as $file) {
            if ($disk->lastModified($file) < $cutoff) {
                $disk->delete($file);

                $deleted++;
            }
        }

        $this->info("Pruned {$deleted} staged upload(s).");

        return self::SUCCESS;
    }
}
