<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Webkul\BagistoApi\Exception\SocialLoginException;

class SocialLoginTokenVerifier
{
    public const PROVIDERS = [
        'google' => ['driver' => 'google',          'enable' => 'enable_google'],
        'facebook' => ['driver' => 'facebook',        'enable' => 'enable_facebook'],
        'linkedin' => ['driver' => 'linkedin-openid', 'enable' => 'enable_linkedin-openid'],
    ];

    protected const CREDENTIAL_KEYS = [
        'google' => 'google',
        'facebook' => 'facebook',
        'linkedin-openid' => 'linkedin',
    ];

    protected const FACEBOOK_DEBUG_TOKEN_URL = 'https://graph.facebook.com/debug_token';

    protected const LINKEDIN_INTROSPECT_URL = 'https://www.linkedin.com/oauth/v2/introspectToken';

    protected const GOOGLE_TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    /**
     * @throws SocialLoginException
     */
    public function driverFor(string $provider): string
    {
        $key = strtolower(trim($provider));

        if (! isset(self::PROVIDERS[$key])) {
            throw new SocialLoginException(
                'PROVIDER_NOT_SUPPORTED',
                trans('bagistoapi::app.graphql.social-login.provider-not-supported'),
            );
        }

        if (! core()->getConfigData('customer.settings.social_login.'.self::PROVIDERS[$key]['enable'])) {
            throw new SocialLoginException(
                'PROVIDER_DISABLED',
                trans('bagistoapi::app.graphql.social-login.provider-disabled'),
            );
        }

        return self::PROVIDERS[$key]['driver'];
    }

    /**
     * The profile behind a token.
     *
     * @return array{id: string, email: ?string, name: ?string, email_verified: bool}
     *
     * @throws SocialLoginException
     */
    public function verify(string $provider, string $driver, ?string $idToken, ?string $accessToken): array
    {
        if (strtolower(trim($provider)) === 'google' && ! empty($idToken)) {
            return $this->verifyGoogleIdToken($idToken);
        }

        if (! empty($accessToken)) {
            $this->assertAccessTokenIssuedToApp($driver, $accessToken);

            return $this->verifyViaSocialite($driver, $accessToken);
        }

        throw new SocialLoginException(
            'SOCIAL_TOKEN_REQUIRED',
            trans('bagistoapi::app.graphql.social-login.token-required'),
        );
    }

    /**
     * Read a Google ID token, refusing one minted for another app.
     *
     * @return array{id: string, email: ?string, name: ?string, email_verified: bool}
     *
     * @throws SocialLoginException
     */
    protected function verifyGoogleIdToken(string $idToken): array
    {
        $response = Http::asForm()->timeout(10)->get(self::GOOGLE_TOKENINFO_URL, ['id_token' => $idToken]);

        if (! $response->successful()) {
            throw new SocialLoginException(
                'SOCIAL_TOKEN_INVALID',
                trans('bagistoapi::app.graphql.social-login.invalid-token'),
            );
        }

        $claims = $response->json();

        $this->assertGoogleAudience($claims['aud'] ?? null);

        if (empty($claims['sub'])) {
            throw new SocialLoginException(
                'SOCIAL_TOKEN_INVALID',
                trans('bagistoapi::app.graphql.social-login.invalid-token'),
            );
        }

        return [
            'id' => (string) $claims['sub'],
            'email' => $claims['email'] ?? null,
            'name' => $claims['name'] ?? trim(($claims['given_name'] ?? '').' '.($claims['family_name'] ?? '')),
            'email_verified' => filter_var($claims['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * @return array{id: string, email: ?string, name: ?string, email_verified: bool}
     *
     * @throws SocialLoginException
     */
    protected function verifyViaSocialite(string $driver, string $accessToken): array
    {
        try {
            $user = Socialite::driver($driver)->userFromToken($accessToken);
        } catch (\Throwable $e) {
            throw new SocialLoginException(
                'SOCIAL_TOKEN_INVALID',
                trans('bagistoapi::app.graphql.social-login.invalid-token'),
            );
        }

        return [
            'id' => (string) $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'email_verified' => (bool) ($user->user['email_verified'] ?? false),
        ];
    }

    /**
     * @throws SocialLoginException
     */
    protected function assertGoogleAudience(?string $audience): void
    {
        if (! $clientIds = $this->googleClientIds()) {
            throw $this->notConfigured();
        }

        if (! in_array($audience, $clientIds, true)) {
            throw new SocialLoginException(
                'SOCIAL_TOKEN_AUDIENCE',
                trans('bagistoapi::app.graphql.social-login.wrong-audience'),
            );
        }
    }

    /**
     * Ask the provider which app an access token was issued to, as the redirect flow implies.
     *
     * @throws SocialLoginException
     */
    protected function assertAccessTokenIssuedToApp(string $driver, string $accessToken): void
    {
        $audience = match ($driver) {
            'google' => $this->googleAccessTokenAudience($accessToken),
            'facebook' => $this->facebookAccessTokenAudience($accessToken),
            'linkedin-openid' => $this->linkedinAccessTokenAudience($accessToken),
        };

        if (! $audience) {
            throw new SocialLoginException(
                'SOCIAL_TOKEN_INVALID',
                trans('bagistoapi::app.graphql.social-login.invalid-token'),
            );
        }

        $clientIds = $driver === 'google'
            ? $this->googleClientIds()
            : array_filter([$this->credentials($driver)['client_id']]);

        if (! in_array($audience, $clientIds, true)) {
            throw new SocialLoginException(
                'SOCIAL_TOKEN_AUDIENCE',
                trans('bagistoapi::app.graphql.social-login.wrong-audience'),
            );
        }
    }

    /**
     * @throws SocialLoginException
     */
    protected function googleAccessTokenAudience(string $accessToken): ?string
    {
        if (! $this->googleClientIds()) {
            throw $this->notConfigured();
        }

        $claims = $this->providerResponse(
            Http::timeout(10)->get(self::GOOGLE_TOKENINFO_URL, ['access_token' => $accessToken])
        );

        $matches = array_intersect([$claims['aud'] ?? null, $claims['azp'] ?? null], $this->googleClientIds());

        return reset($matches) ?: ($claims['aud'] ?? null);
    }

    /**
     * @throws SocialLoginException
     */
    protected function facebookAccessTokenAudience(string $accessToken): ?string
    {
        ['client_id' => $clientId, 'client_secret' => $clientSecret] = $this->credentials('facebook');

        if (! $clientId || ! $clientSecret) {
            throw $this->notConfigured();
        }

        $data = $this->providerResponse(Http::timeout(10)->get(self::FACEBOOK_DEBUG_TOKEN_URL, [
            'input_token' => $accessToken,
            'access_token' => $clientId.'|'.$clientSecret,
        ]))['data'] ?? [];

        return ! empty($data['is_valid']) ? (string) ($data['app_id'] ?? '') : null;
    }

    /**
     * @throws SocialLoginException
     */
    protected function linkedinAccessTokenAudience(string $accessToken): ?string
    {
        ['client_id' => $clientId, 'client_secret' => $clientSecret] = $this->credentials('linkedin-openid');

        if (! $clientId || ! $clientSecret) {
            throw $this->notConfigured();
        }

        $data = $this->providerResponse(Http::asForm()->timeout(10)->post(self::LINKEDIN_INTROSPECT_URL, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'token' => $accessToken,
        ]));

        return ! empty($data['active']) ? (string) ($data['client_id'] ?? '') : null;
    }

    /**
     * @throws SocialLoginException
     */
    protected function providerResponse(Response $response): array
    {
        if (! $response->successful()) {
            throw new SocialLoginException(
                'SOCIAL_TOKEN_INVALID',
                trans('bagistoapi::app.graphql.social-login.invalid-token'),
            );
        }

        return (array) $response->json();
    }

    /**
     * Client credentials read the way core's SocialiteManager reads them.
     *
     * @return array{client_id: ?string, client_secret: ?string}
     */
    protected function credentials(string $driver): array
    {
        $key = self::CREDENTIAL_KEYS[$driver];

        $clientId = core()->getConfigData("customer.settings.social_login.{$key}_client_id");
        $clientSecret = core()->getConfigData("customer.settings.social_login.{$key}_client_secret");

        if (! $clientId && ! $clientSecret) {
            $clientId = config("services.{$driver}.client_id");
            $clientSecret = config("services.{$driver}.client_secret");
        }

        return ['client_id' => $clientId ?: null, 'client_secret' => $clientSecret ?: null];
    }

    /**
     * @return array<int, string>
     */
    protected function googleClientIds(): array
    {
        $ids = [core()->getConfigData('customer.settings.social_login.google_client_id')];

        if (! array_filter($ids)) {
            $ids = explode(',', (string) config('services.google.client_id'));
        }

        return array_values(array_unique(array_filter(array_map('trim', $ids))));
    }

    protected function notConfigured(): SocialLoginException
    {
        return new SocialLoginException(
            'PROVIDER_NOT_CONFIGURED',
            trans('bagistoapi::app.graphql.social-login.not-configured'),
        );
    }
}
