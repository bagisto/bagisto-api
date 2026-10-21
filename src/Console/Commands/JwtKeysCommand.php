<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtKeyStore;

class JwtKeysCommand extends Command
{
    /**
     * The signature.
     */
    protected $signature = 'bagisto-api:jwt-keys
        {--rotate : Make a new key the signing key and keep the old public key for verification}
        {--reissue-api-tokens : Sign every active API token that uses an older key again with the current key}';

    /**
     * The description.
     */
    protected $description = 'Create or rotate the ES256 key pair that signs admin API access tokens and API tokens.';

    /**
     * Handle the command.
     */
    public function handle(JwtKeyStore $keys, AdminApiTokenIssuer $issuer): int
    {
        if ($keys->usesConfiguredKeys()) {
            return $this->handleConfiguredKeys($keys, $issuer);
        }

        if (! $keys->exists()) {
            $kid = $keys->create();

            $this->info("Created the JWT signing key {$kid} in {$keys->directory()}.");

            return self::SUCCESS;
        }

        if ($this->option('rotate')) {
            $kid = $keys->rotate();

            $this->info("Rotated the JWT signing key. New tokens are signed with {$kid}; the previous public key is kept for verification.");
        } elseif (! $this->option('reissue-api-tokens')) {
            $this->info("JWT signing keys already exist (current key {$keys->signingKid()}). Use --rotate to replace them.");

            return self::SUCCESS;
        }

        return $this->handleOlderTokens($issuer);
    }

    /**
     * Report on keys provided through the configuration, which this command cannot change.
     */
    protected function handleConfiguredKeys(JwtKeyStore $keys, AdminApiTokenIssuer $issuer): int
    {
        if ($this->option('rotate')) {
            $this->error('The JWT keys come from BAGISTO_API_JWT_PRIVATE_KEY. Rotate them by setting a new key pair there and moving the old public key to BAGISTO_API_JWT_PREVIOUS_PUBLIC_KEYS.');

            return self::FAILURE;
        }

        $this->info("The JWT keys come from the configuration (current key {$keys->signingKid()}).");

        return $this->handleOlderTokens($issuer);
    }

    /**
     * List, or with --reissue-api-tokens sign again, the API tokens that still use an older key.
     */
    protected function handleOlderTokens(AdminApiTokenIssuer $issuer): int
    {
        $tokens = $issuer->tokensSignedByOlderKeys();

        if ($tokens->isEmpty()) {
            return self::SUCCESS;
        }

        if (! $this->option('reissue-api-tokens')) {
            $this->warn("{$tokens->count()} active API token(s) are signed by an older key and stop working once it is dropped. Run again with --reissue-api-tokens to sign them with the current key.");

            $this->table(['ID', 'Name', 'Admin', 'Domain', 'Key'], $this->rows($tokens));

            return self::SUCCESS;
        }

        $this->warn('The API tokens below were signed again. Each integration must switch to its new token; it is shown only once.');

        $this->table(['ID', 'Name', 'Admin', 'Domain', 'New token'], $tokens->map(fn ($token) => [
            $token->id,
            $token->name,
            $token->admin?->email,
            $token->domain,
            $issuer->reissue($token),
        ])->all());

        return self::SUCCESS;
    }

    /**
     * The table rows for API tokens signed by an older key.
     */
    protected function rows(Collection $tokens): array
    {
        return $tokens->map(fn ($token) => [
            $token->id,
            $token->name,
            $token->admin?->email,
            $token->domain,
            $token->signing_kid,
        ])->all();
    }
}
