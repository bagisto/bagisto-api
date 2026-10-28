<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenService;

class PruneAdminTokensCommand extends Command
{
    /**
     * The signature.
     */
    protected $signature = 'bagisto-api:prune-admin-tokens';

    /**
     * The description.
     */
    protected $description = 'Delete expired admin API refresh tokens and two-factor challenges, long-ended sessions and retired JWT keys no longer needed.';

    /**
     * Handle the command.
     */
    public function handle(AdminApiTokenService $tokenService): int
    {
        $pruned = $tokenService->prune();

        $this->info("Pruned {$pruned['refresh_tokens']} refresh token(s), {$pruned['sessions']} session(s) and {$pruned['two_factor_challenges']} two-factor challenge(s).");

        foreach ($pruned['keys'] as $kid) {
            $this->info("Dropped the retired JWT key {$kid}.");
        }

        return self::SUCCESS;
    }
}
