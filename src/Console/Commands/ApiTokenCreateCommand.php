<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;

class ApiTokenCreateCommand extends Command
{
    /**
     * The value of --expires for a token that never expires.
     */
    public const NEVER = 'never';

    /**
     * The signature.
     */
    protected $signature = 'bagisto-api:api-token:create
        {--admin= : The id or email of the admin user the token acts as}
        {--name= : The token name, at most 100 characters}
        {--domain= : The domain the integration calls from, such as erp.example.com}
        {--expires= : The last day the token works (YYYY-MM-DD), or "never"; defaults to never when allowed}
        {--only-token : Print only the token, for scripts}';

    /**
     * The description.
     */
    protected $description = 'Create an admin API token bound to a domain and print it once.';

    /**
     * Handle the command.
     */
    public function handle(AdminRepository $adminRepository, AdminApiTokenIssuer $issuer): int
    {
        $admin = $this->admin($adminRepository);

        if (! $admin) {
            $this->error('No admin user matches --admin. Pass the id or email of an existing admin.');

            return self::FAILURE;
        }

        $expires = (string) ($this->option('expires') ?: self::NEVER);

        $expiresAt = $expires === self::NEVER
            ? null
            : $this->expiry($expires);

        if (
            $expires !== self::NEVER
            && $expiresAt === null
        ) {
            $this->error('--expires must be a date in the form YYYY-MM-DD, or "never".');

            return self::FAILURE;
        }

        try {
            $issued = $issuer->create(
                null,
                $admin,
                (string) $this->option('name'),
                (string) $this->option('domain'),
                $expiresAt,
                Request::capture(),
            );
        } catch (InvalidInputException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($this->option('only-token')) {
            $this->line($issued->plainText);

            return self::SUCCESS;
        }

        $this->info("Created API token {$issued->token->id} \"{$issued->token->name}\" for {$admin->email}, bound to {$issued->token->domain}, ".($issued->token->expires_at ? 'expiring '.$issued->token->expires_at->toDateTimeString() : 'without expiry').'.');

        $this->warn('Copy the token now; it is shown only once.');

        $this->line($issued->plainText);

        return self::SUCCESS;
    }

    /**
     * The admin --admin names by id or email.
     */
    protected function admin(AdminRepository $adminRepository): ?Admin
    {
        $value = trim((string) $this->option('admin'));

        if ($value === '') {
            return null;
        }

        return ctype_digit($value)
            ? $adminRepository->with('role')->find((int) $value)
            : $adminRepository->with('role')->findOneByField('email', $value);
    }

    /**
     * The end of the day --expires names, or null when it is not a valid date.
     */
    protected function expiry(string $value): ?Carbon
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts) !== 1) {
            return null;
        }

        if (! checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            return null;
        }

        return Carbon::createFromFormat('!Y-m-d', $value)->endOfDay();
    }
}
