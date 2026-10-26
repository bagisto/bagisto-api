<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;

class PruneAuditsCommand extends Command
{
    /**
     * The signature.
     */
    protected $signature = 'bagisto-api:prune-audits {--days= : Override the configured audit retention}';

    /**
     * The description.
     */
    protected $description = 'Delete admin-API audit history older than the retention period.';

    /**
     * Handle the request.
     */
    public function handle(AdminApiAuthSettings $settings): int
    {
        $days = $this->option('days') ?? $settings->auditRetentionDays();

        if (
            $days === null
            || ! is_numeric($days)
        ) {
            $this->info('No audit retention period configured. Nothing pruned.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays((int) $days);
        $deleted = AdminApiAudit::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} audit row(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
