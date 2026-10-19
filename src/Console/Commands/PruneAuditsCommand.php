<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;

class PruneAuditsCommand extends Command
{
    /**
     * The signature.
     */
    protected $signature = 'bagisto-api:prune-audits {--days= : Override bagistoapi.audit.retention_days}';

    /**
     * The description.
     */
    protected $description = 'Delete admin-API audit history older than the retention period.';

    /**
     * Handle the request.
     */
    public function handle(): int
    {
        $days = $this->option('days') ?? config('bagistoapi.audit.retention_days');

        if (
            $days === null
            || ! is_numeric($days)
        ) {
            $this->info('No retention period configured (bagistoapi.audit.retention_days is null). Nothing pruned.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays((int) $days);
        $deleted = AdminApiAudit::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} audit row(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
