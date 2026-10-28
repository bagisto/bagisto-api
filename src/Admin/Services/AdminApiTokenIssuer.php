<?php

namespace Webkul\BagistoApi\Admin\Services;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\User\Models\Admin;

class AdminApiTokenIssuer
{
    /**
     * The number of trailing characters of a token kept to recognise it in a list.
     */
    public const PREVIEW_LENGTH = 8;

    /**
     * The longest name an API token may have.
     */
    public const NAME_MAX_LENGTH = 100;

    /**
     * The ACL key that allows creating API tokens.
     */
    public const PERMISSION_CREATE = 'settings.api_tokens.create';

    /**
     * The ACL key that allows creating and revoking the API tokens of other admins.
     */
    public const PERMISSION_MANAGE_OTHERS = 'settings.api_tokens.manage_others';

    /**
     * Create a new API token issuer.
     */
    public function __construct(
        protected AdminApiTokenRepository $apiTokenRepository,
        protected JwtSigner $signer,
        protected DomainBinding $domainBinding,
        protected AdminApiAuthSettings $settings,
        protected AdminApiAuthAuditor $auditor,
    ) {}

    /**
     * Whether the creator may create an API token acting as the owner, under the escalation rule.
     */
    public function canCreateFor(Admin $creator, Admin $owner): bool
    {
        if (! AdminAuthHelper::hasPermission($creator, self::PERMISSION_CREATE)) {
            return false;
        }

        if ((int) $creator->id === (int) $owner->id) {
            return true;
        }

        return AdminAuthHelper::hasPermission($creator, self::PERMISSION_MANAGE_OTHERS)
            && AdminAuthHelper::canGrantRole($creator, $owner->role);
    }

    /**
     * Whether the admin may see, rename and revoke the API token: their own, or any with manage_others.
     */
    public function canManage(Admin $actor, AdminApiToken $token): bool
    {
        return (int) $token->admin_id === (int) $actor->id
            || $this->canManageOthers($actor);
    }

    /**
     * Whether the admin may see and manage the API tokens of every admin.
     */
    public function canManageOthers(Admin $actor): bool
    {
        return AdminAuthHelper::hasPermission($actor, self::PERMISSION_MANAGE_OTHERS);
    }

    /**
     * Create an API token acting as the owner and return it with its string; the creator is null for the console.
     *
     * @throws AuthorizationException When the creator may not create a token for the owner.
     * @throws InvalidInputException When the name, domain or expiry cannot be used.
     */
    public function create(?Admin $creator, Admin $owner, string $name, string $domain, ?CarbonInterface $expiresAt, Request $request): IssuedAdminApiToken
    {
        if (
            $creator !== null
            && ! $this->canCreateFor($creator, $owner)
        ) {
            throw new AuthorizationException(__('bagistoapi::app.admin.api-tokens.errors.cannot-create-for-admin'));
        }

        $attributes = [
            'admin_id' => (int) $owner->id,
            'created_by_admin_id' => $creator?->id,
            'name' => $this->validName($name),
            'domain' => $this->validDomain($domain),
            'expires_at' => $this->validExpiry($expiresAt),
        ];

        Event::dispatch('bagistoapi.admin.api-token.create.before', $attributes);

        $attributes['jti'] = bin2hex(random_bytes(16));

        $plainText = $this->signer->signApiToken(
            $attributes['admin_id'],
            $attributes['jti'],
            $attributes['domain'],
            Carbon::now(),
            $attributes['expires_at'],
        );

        $token = $this->apiTokenRepository->create(array_merge($attributes, [
            'token_preview' => $this->preview($plainText),
            'signing_kid' => $this->signer->signingKid(),
        ]));

        Event::dispatch('bagistoapi.admin.api-token.create.after', $token);

        $this->auditor->record(AdminApiAuthAuditor::EVENT_API_TOKEN_CREATED, $creator, $token, $request, [
            'admin_id' => (int) $owner->id,
            'domain' => $token->domain,
            'expires_at' => $token->expires_at?->toIso8601String(),
        ]);

        return new IssuedAdminApiToken($token, $plainText);
    }

    /**
     * Give an API token a new name; its domain and expiry never change.
     *
     * @throws AuthorizationException When the actor may not manage the token.
     * @throws InvalidInputException When the name cannot be used.
     */
    public function rename(Admin $actor, AdminApiToken $token, string $name, Request $request): AdminApiToken
    {
        $this->authorizeManage($actor, $token);

        $name = $this->validName($name);

        $previousName = $token->name;

        Event::dispatch('bagistoapi.admin.api-token.update.before', $token);

        $token = $this->apiTokenRepository->rename($token, $name);

        Event::dispatch('bagistoapi.admin.api-token.update.after', $token);

        $this->auditor->record(AdminApiAuthAuditor::EVENT_API_TOKEN_RENAMED, $actor, $token, $request, [
            'previous_name' => $previousName,
        ]);

        return $token;
    }

    /**
     * Revoke an API token, effective on its next request; the caller checks settings.api_tokens.delete.
     *
     * @throws AuthorizationException When the actor may not manage the token.
     * @throws InvalidInputException When the token is already revoked.
     */
    public function revoke(Admin $actor, AdminApiToken $token, Request $request): AdminApiToken
    {
        $this->authorizeManage($actor, $token);

        if ($token->isRevoked()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.api-tokens.errors.already-revoked'), 409);
        }

        Event::dispatch('bagistoapi.admin.api-token.revoke.before', $token);

        $token = $this->apiTokenRepository->revoke($token, AdminApiToken::REVOKED_BY_ADMIN, (int) $actor->id);

        Event::dispatch('bagistoapi.admin.api-token.revoke.after', $token);

        $this->auditor->record(AdminApiAuthAuditor::EVENT_API_TOKEN_REVOKED, $actor, $token, $request, [
            'admin_id' => (int) $token->admin_id,
        ]);

        return $token;
    }

    /**
     * The active API tokens that are not signed by the current key, and break once their key is dropped.
     */
    public function tokensSignedByOlderKeys(): Collection
    {
        return $this->apiTokenRepository->activeNotSignedWith($this->signer->signingKid());
    }

    /**
     * Sign an API token again with the current key, keeping its id, domain and expiry, and return the new string.
     */
    public function reissue(AdminApiToken $token): string
    {
        Event::dispatch('bagistoapi.admin.api-token.reissue.before', $token);

        $jwt = $this->signer->signApiToken(
            $token->admin_id,
            $token->jti,
            $token->domain,
            Carbon::now(),
            $token->expires_at,
        );

        $token = $this->apiTokenRepository->markReissued($token, $this->signer->signingKid(), $this->preview($jwt));

        Event::dispatch('bagistoapi.admin.api-token.reissue.after', $token);

        return $jwt;
    }

    /**
     * The trailing characters of a token string, shown to recognise it in a list.
     */
    public function preview(string $jwt): string
    {
        return substr($jwt, -self::PREVIEW_LENGTH);
    }

    /**
     * Fail unless the actor may manage the token.
     *
     * @throws AuthorizationException
     */
    protected function authorizeManage(Admin $actor, AdminApiToken $token): void
    {
        if (! $this->canManage($actor, $token)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.api-tokens.errors.cannot-manage'));
        }
    }

    /**
     * The trimmed name, which must be present and at most 100 characters.
     *
     * @throws InvalidInputException
     */
    protected function validName(string $name): string
    {
        $name = trim($name);

        if (
            $name === ''
            || mb_strlen($name) > self::NAME_MAX_LENGTH
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.api-tokens.errors.invalid-name'), 422);
        }

        return $name;
    }

    /**
     * The normalised host of a domain or URL.
     *
     * @throws InvalidInputException When it is an IP address, a wildcard or has no host.
     */
    protected function validDomain(string $domain): string
    {
        $host = $this->domainBinding->normalize($domain);

        if ($host === null) {
            throw new InvalidInputException(__('bagistoapi::app.admin.api-tokens.errors.invalid-domain'), 422);
        }

        return $host;
    }

    /**
     * The expiry, which must be in the future, or null for no expiry when that is allowed.
     *
     * @throws InvalidInputException
     */
    protected function validExpiry(?CarbonInterface $expiresAt): ?CarbonInterface
    {
        if ($expiresAt === null) {
            if (! $this->settings->allowNonExpiringApiTokens()) {
                throw new InvalidInputException(__('bagistoapi::app.admin.api-tokens.errors.expiry-required'), 422);
            }

            return null;
        }

        if (! $expiresAt->isFuture()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.api-tokens.errors.expiry-in-past'), 422);
        }

        return $expiresAt;
    }
}
