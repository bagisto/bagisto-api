<?php

namespace Webkul\BagistoApi\Admin\Services\Jwt;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;

class JwtVerifier
{
    /**
     * Create a new verifier.
     */
    public function __construct(
        protected JwtKeyStore $keys,
        protected JwtSigner $signer,
    ) {}

    /**
     * Whether a bearer value has the three dot-separated parts of a compact JWT.
     */
    public function looksLikeJwt(string $token): bool
    {
        return preg_match('/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $token) === 1;
    }

    /**
     * Verify an access token or an API token and return its claims, with the header's key id as kid.
     *
     * @throws InvalidJwtException
     * @throws ExpiredJwtException
     */
    public function verify(string $token): array
    {
        return $this->verifyWith($token, fn (array $claims) => $this->assertClaims($claims));
    }

    /**
     * Verify a storefront customer access token and return its claims, with the header's key id as kid.
     *
     * @throws InvalidJwtException
     * @throws ExpiredJwtException
     */
    public function verifyCustomerAccessToken(string $token): array
    {
        return $this->verifyWith($token, fn (array $claims) => $this->assertCustomerAccessClaims($claims));
    }

    /**
     * Verify a token that must be an access token and return its claims.
     */
    public function verifyAccessToken(string $token): array
    {
        $claims = $this->verify($token);

        if ($claims['typ'] !== JwtSigner::TYPE_ACCESS) {
            throw new InvalidJwtException;
        }

        return $claims;
    }

    /**
     * Verify a token that must be an API token and return its claims.
     */
    public function verifyApiToken(string $token): array
    {
        $claims = $this->verify($token);

        if ($claims['typ'] !== JwtSigner::TYPE_API) {
            throw new InvalidJwtException;
        }

        return $claims;
    }

    /**
     * Check a token's header and signature, run the claims check for its kind, then its key and times.
     *
     * @throws InvalidJwtException
     * @throws ExpiredJwtException
     */
    protected function verifyWith(string $token, callable $assertClaims): array
    {
        if (! $this->looksLikeJwt($token)) {
            throw new InvalidJwtException;
        }

        $jws = $this->decode($token);

        $header = $jws->getSignature(0)->getProtectedHeader();

        $this->assertHeader($jws, $header);
        $this->assertSignature($jws, (string) $header['kid']);

        $claims = $this->claims($jws);

        $assertClaims($claims);
        $this->assertKeyServesAccessTokens((string) $header['kid'], $claims['typ']);
        $this->assertTimes($claims);

        $claims['kid'] = (string) $header['kid'];

        return $claims;
    }

    /**
     * Unserialize a compact JWT holding exactly one signature.
     */
    protected function decode(string $token): JWS
    {
        try {
            $jws = (new CompactSerializer)->unserialize($token);
        } catch (\Throwable $exception) {
            throw new InvalidJwtException;
        }

        if ($jws->countSignatures() !== 1) {
            throw new InvalidJwtException;
        }

        return $jws;
    }

    /**
     * Accept only an ES256 header with a known key id, no critical extensions and no unprotected values.
     */
    protected function assertHeader(JWS $jws, array $header): void
    {
        if (
            ($header['alg'] ?? null) !== JwtSigner::ALGORITHM
            || ($header['typ'] ?? null) !== 'JWT'
            || ! is_string($header['kid'] ?? null)
            || $header['kid'] === ''
            || array_key_exists('crit', $header)
            || array_key_exists('b64', $header)
            || $jws->getSignature(0)->getHeader() !== []
            || $jws->isPayloadDetached()
        ) {
            throw new InvalidJwtException;
        }
    }

    /**
     * Check the signature against the public key the header names.
     */
    protected function assertSignature(JWS $jws, string $kid): void
    {
        $key = $this->keys->verificationKey($kid);

        if ($key === null) {
            throw new InvalidJwtException;
        }

        try {
            $valid = (new JWSVerifier(new AlgorithmManager([new ES256])))->verifyWithKey($jws, $key, 0);
        } catch (\Throwable $exception) {
            $valid = false;
        }

        if (! $valid) {
            throw new InvalidJwtException;
        }
    }

    /**
     * Accept an access token only from the current key or one retired within the longest access token lifetime.
     */
    protected function assertKeyServesAccessTokens(string $kid, string $type): void
    {
        if (
            ! in_array($type, [JwtSigner::TYPE_ACCESS, JwtSigner::TYPE_CUSTOMER_ACCESS], true)
            || $kid === $this->keys->signingKid()
        ) {
            return;
        }

        $retiredAt = $this->keys->retiredKeys()[$kid] ?? null;

        $cutoff = now()
            ->subMinutes(JwtSigner::longestAccessTokenMinutes())
            ->subSeconds(max(0, (int) config('bagistoapi.admin_auth.jwt.leeway', 30)));

        if (
            $retiredAt === null
            || $retiredAt->lessThan($cutoff)
        ) {
            throw new InvalidJwtException;
        }
    }

    /**
     * Decode the payload into its claims.
     */
    protected function claims(JWS $jws): array
    {
        try {
            $claims = json_decode((string) $jws->getPayload(), true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidJwtException;
        }

        if (! is_array($claims)) {
            throw new InvalidJwtException;
        }

        return $claims;
    }

    /**
     * Check the issuer, audience, subject, type and ids the token kind requires.
     */
    protected function assertClaims(array $claims): void
    {
        $type = $claims['typ'] ?? null;

        if (
            ($claims['iss'] ?? null) !== $this->signer->issuer()
            || ! in_array($type, [JwtSigner::TYPE_ACCESS, JwtSigner::TYPE_API], true)
            || ! $this->isPositiveInt($claims['sub'] ?? null)
            || ! is_string($claims['jti'] ?? null)
            || $claims['jti'] === ''
            || ! is_int($claims['iat'] ?? null)
            || ! is_int($claims['nbf'] ?? null)
            || (
                array_key_exists('exp', $claims)
                && ! is_int($claims['exp'])
            )
        ) {
            throw new InvalidJwtException;
        }

        if ($type === JwtSigner::TYPE_ACCESS) {
            $this->assertAccessClaims($claims);

            return;
        }

        $this->assertApiClaims($claims);
    }

    /**
     * Require the customer access type and audience, a customer, a session id and an expiry.
     */
    protected function assertCustomerAccessClaims(array $claims): void
    {
        if (
            ($claims['iss'] ?? null) !== $this->signer->issuer()
            || ($claims['typ'] ?? null) !== JwtSigner::TYPE_CUSTOMER_ACCESS
            || ($claims['aud'] ?? null) !== $this->signer->customerAudience()
            || ! $this->isPositiveInt($claims['sub'] ?? null)
            || ! $this->isPositiveInt($claims['sid'] ?? null)
            || ! is_string($claims['jti'] ?? null)
            || $claims['jti'] === ''
            || ! is_int($claims['iat'] ?? null)
            || ! is_int($claims['nbf'] ?? null)
            || ! is_int($claims['exp'] ?? null)
        ) {
            throw new InvalidJwtException;
        }
    }

    /**
     * Require the access token audience, a session id and an expiry.
     */
    protected function assertAccessClaims(array $claims): void
    {
        if (
            ($claims['aud'] ?? null) !== $this->signer->audience()
            || ! $this->isPositiveInt($claims['sid'] ?? null)
            || ! array_key_exists('exp', $claims)
        ) {
            throw new InvalidJwtException;
        }
    }

    /**
     * Require a domain audience and no session id.
     */
    protected function assertApiClaims(array $claims): void
    {
        if (
            ! is_string($claims['aud'] ?? null)
            || $claims['aud'] === ''
            || $claims['aud'] === $this->signer->audience()
            || array_key_exists('sid', $claims)
        ) {
            throw new InvalidJwtException;
        }
    }

    /**
     * Reject a token not valid yet, and report one past its expiry, allowing the configured leeway.
     */
    protected function assertTimes(array $claims): void
    {
        $now = now()->getTimestamp();

        $leeway = max(0, (int) config('bagistoapi.admin_auth.jwt.leeway', 30));

        if (
            $claims['nbf'] > $now + $leeway
            || $claims['iat'] > $now + $leeway
        ) {
            throw new InvalidJwtException;
        }

        if (
            isset($claims['exp'])
            && $now > $claims['exp'] + $leeway
        ) {
            throw new ExpiredJwtException($claims['typ']);
        }
    }

    /**
     * Whether a claim is a positive integer.
     */
    protected function isPositiveInt(mixed $value): bool
    {
        return is_int($value)
            && $value > 0;
    }
}
