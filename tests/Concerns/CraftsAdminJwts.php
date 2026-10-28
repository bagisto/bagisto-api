<?php

namespace Webkul\BagistoApi\Tests\Concerns;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use RuntimeException;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtKeyStore;

trait CraftsAdminJwts
{
    /**
     * The directory holding the test JWT key pair.
     */
    protected function jwtFixtureDirectory(): string
    {
        return dirname(__DIR__).'/Fixtures/jwt';
    }

    /**
     * The test signing key, as PEM.
     */
    protected function jwtFixturePrivateKey(): string
    {
        return (string) file_get_contents($this->jwtFixtureDirectory().'/private.pem');
    }

    /**
     * The test verification key, as PEM.
     */
    protected function jwtFixturePublicKey(): string
    {
        return (string) file_get_contents($this->jwtFixtureDirectory().'/public.pem');
    }

    /**
     * Make the JWT key store sign and verify with the test key pair from the configuration.
     */
    protected function useFixtureJwtKeys(): void
    {
        config([
            'bagistoapi.admin_auth.jwt.private_key' => $this->jwtFixturePrivateKey(),
            'bagistoapi.admin_auth.jwt.public_key' => $this->jwtFixturePublicKey(),
            'bagistoapi.admin_auth.jwt.kid' => null,
            'bagistoapi.admin_auth.jwt.previous_public_keys' => [],
        ]);
    }

    /**
     * The key id tokens are signed with under the test key pair.
     */
    protected function fixtureKid(): string
    {
        return app(JwtKeyStore::class)->signingKid();
    }

    /**
     * A new ES256 private key that the store does not know, as PEM.
     */
    protected function foreignPrivateKey(): string
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);

        if (
            $key === false
            || ! openssl_pkey_export($key, $pem)
        ) {
            throw new RuntimeException('Unable to generate a test EC key.');
        }

        return $pem;
    }

    /**
     * Sign claims with ES256 under any header, using the test key unless another private key is given.
     */
    protected function signJwt(array $claims, array $header = [], ?string $privateKey = null): string
    {
        $jwk = JWKFactory::createFromKey($privateKey ?? $this->jwtFixturePrivateKey(), null, [
            'alg' => 'ES256',
            'use' => 'sig',
        ]);

        $jws = (new JWSBuilder(new AlgorithmManager([new ES256])))
            ->create()
            ->withPayload(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))
            ->addSignature($jwk, array_merge([
                'alg' => 'ES256',
                'typ' => 'JWT',
                'kid' => $this->fixtureKid(),
            ], $header))
            ->build();

        return (new CompactSerializer)->serialize($jws, 0);
    }

    /**
     * An unsigned token whose header claims the "none" algorithm.
     */
    protected function unsignedJwt(array $claims): string
    {
        return $this->base64Url(json_encode(['alg' => 'none', 'typ' => 'JWT', 'kid' => $this->fixtureKid()]))
            .'.'.$this->base64Url(json_encode($claims))
            .'.';
    }

    /**
     * A token signed with HS256 using the public key as the shared secret.
     */
    protected function hs256WithPublicKey(array $claims): string
    {
        $input = $this->base64Url(json_encode(['alg' => 'HS256', 'typ' => 'JWT', 'kid' => $this->fixtureKid()]))
            .'.'.$this->base64Url(json_encode($claims));

        return $input.'.'.$this->base64Url(hash_hmac('sha256', $input, $this->jwtFixturePublicKey(), true));
    }

    /**
     * The same token with its payload replaced, keeping the original signature.
     */
    protected function withTamperedPayload(string $token, array $changes): string
    {
        [$header, , $signature] = explode('.', $token);

        $claims = array_merge($this->jwtClaims($token), $changes);

        return $header.'.'.$this->base64Url(json_encode($claims)).'.'.$signature;
    }

    /**
     * The decoded header of a compact token.
     */
    protected function jwtHeader(string $token): array
    {
        return json_decode($this->base64UrlDecode(explode('.', $token)[0]), true);
    }

    /**
     * The decoded claims of a compact token.
     */
    protected function jwtClaims(string $token): array
    {
        return json_decode($this->base64UrlDecode(explode('.', $token)[1]), true);
    }

    /**
     * Encode bytes as unpadded base64url.
     */
    protected function base64Url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    /**
     * Decode unpadded base64url.
     */
    protected function base64UrlDecode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'));
    }
}
