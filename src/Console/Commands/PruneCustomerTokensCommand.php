<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiTokenService;

class PruneCustomerTokensCommand extends Command
{
    /**
     * The signature.
     */
    protected $signature = 'bagisto-api:prune-customer-tokens';

    /**
     * The description.
     */
    protected $description = 'Delete expired storefront customer refresh tokens and long-ended customer sessions.';

    /**
     * Handle the command.
     */
    public function handle(CustomerApiTokenService $tokenService): int
    {
        $pruned = $tokenService->prune();

        $this->info("Pruned {$pruned['refresh_tokens']} refresh token(s) and {$pruned['sessions']} session(s).");

        return self::SUCCESS;
    }
}
