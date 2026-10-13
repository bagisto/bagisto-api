<?php

namespace Webkul\BagistoApi\Services;

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
        } catch (\Throwable $exception) {
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
            throw new SocialLoginException(
                'SOCIAL_PROVIDER_NOT_CONFIGURED',
                trans('bagistoapi::app.graphql.social-login.google-not-configured'),
            );
        }

        if (! in_array($audience, $clientIds, true)) {
            throw new SocialLoginException(
                'SOCIAL_TOKEN_AUDIENCE',
                trans('bagistoapi::app.graphql.social-login.wrong-audience'),
            );
        }

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
}
