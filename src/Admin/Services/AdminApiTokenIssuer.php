<?php

namespace Webkul\BagistoApi\Admin\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;

class AdminApiTokenIssuer
{
    /**
     * The number of trailing characters of a token kept to recognise it in a list.
     */
    public const PREVIEW_LENGTH = 8;

    /**
     * Create a new API token issuer.
     */
    public function __construct(
        protected AdminApiTokenRepository $apiTokenRepository,
        protected JwtSigner $signer,
    ) {}

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
}
