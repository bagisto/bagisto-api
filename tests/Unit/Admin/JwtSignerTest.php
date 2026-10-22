<?php

namespace Webkul\BagistoApi\Tests\Unit\Admin;

use Illuminate\Support\Carbon;
use Webkul\BagistoApi\Admin\Services\Jwt\ExpiredJwtException;
use Webkul\BagistoApi\Admin\Services\Jwt\InvalidJwtException;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtKeyStore;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtVerifier;
use Webkul\BagistoApi\Tests\BagistoApiTest;
use Webkul\BagistoApi\Tests\Concerns\CraftsAdminJwts;

class JwtSignerTest extends BagistoApiTest
{
    use CraftsAdminJwts;

    // ========================================================================
    // Signing
    // ========================================================================

    /**
     * Test an access token is an ES256 JWT with the documented header and claims.
     */
    public function test_an_access_token_has_the_documented_header_and_claims(): void
    {
        $now = Carbon::now();

        $token = $this->signer()->signAccessToken(7, 12, $now, $now->copy()->addMinutes(15));

        $this->assertSame(['alg' => 'ES256', 'typ' => 'JWT', 'kid' => $this->fixtureKid()], $this->jwtHeader($token));

        $claims = $this->verifier()->verifyAccessToken($token);

        $this->assertSame('https://store.test', $claims['iss']);
        $this->assertSame('bagisto-admin-api', $claims['aud']);
        $this->assertSame(7, $claims['sub']);
        $this->assertSame(12, $claims['sid']);
        $this->assertSame('access', $claims['typ']);
        $this->assertSame($now->getTimestamp() + 900, $claims['exp']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $claims['jti']);
    }

    /**
     * Test an API token names its domain as audience, and has no exp when it never expires.
     */
    public function test_an_api_token_names_its_domain_and_may_have_no_expiry(): void
    {
        $now = Carbon::now();

        $forever = $this->signer()->signApiToken(3, 'abc123', 'erp.example.com', $now, null);
        $dated = $this->signer()->signApiToken(3, 'abc124', 'erp.example.com', $now, $now->copy()->addDays(30));

        $claims = $this->verifier()->verifyApiToken($forever);

        $this->assertSame('erp.example.com', $claims['aud']);
        $this->assertSame('api', $claims['typ']);
        $this->assertSame('abc123', $claims['jti']);
        $this->assertArrayNotHasKey('exp', $claims);
        $this->assertArrayNotHasKey('sid', $claims);

        $this->assertSame($now->getTimestamp() + 30 * 86400, $this->verifier()->verifyApiToken($dated)['exp']);
    }

    /**
     * Test each kind is refused where the other is required.
     */
    public function test_each_kind_is_refused_where_the_other_is_required(): void
    {
        $now = Carbon::now();

        $access = $this->signer()->signAccessToken(7, 12, $now, $now->copy()->addMinutes(15));
        $api = $this->signer()->signApiToken(3, 'abc123', 'erp.example.com', $now, null);

        $this->assertRejected(fn () => $this->verifier()->verifyApiToken($access));
        $this->assertRejected(fn () => $this->verifier()->verifyAccessToken($api));
    }

    // ========================================================================
    // Verification
    // ========================================================================

    /**
     * Test forged or malformed tokens are rejected.
     */
    public function test_forged_and_malformed_tokens_are_rejected(): void
    {
        $now = Carbon::now();

        $token = $this->signer()->signAccessToken(7, 12, $now, $now->copy()->addMinutes(15));
        $claims = $this->jwtClaims($token);

        $cases = [
            'garbage' => 'not-a-jwt',
            'two parts' => implode('.', array_slice(explode('.', $token), 0, 2)),
            'a tampered payload' => $this->withTamperedPayload($token, ['sub' => 8]),
            'a signature by another key' => $this->signJwt($claims, [], $this->foreignPrivateKey()),
            'alg none' => $this->unsignedJwt($claims),
            'HS256 with the public key' => $this->hs256WithPublicKey($claims),
            'an unknown kid' => $this->signJwt($claims, ['kid' => 'unknown']),
            'an empty kid' => $this->signJwt($claims, ['kid' => '']),
            'a crit header' => $this->signJwt($claims, ['crit' => ['exp'], 'exp' => 1]),
            'a wrong typ header' => $this->signJwt($claims, ['typ' => 'JOSE']),
            'a wrong issuer' => $this->signJwt(array_merge($claims, ['iss' => 'https://evil.test'])),
            'a wrong audience' => $this->signJwt(array_merge($claims, ['aud' => 'erp.example.com'])),
            'an unknown typ' => $this->signJwt(array_merge($claims, ['typ' => 'refresh'])),
            'a string subject' => $this->signJwt(array_merge($claims, ['sub' => '7'])),
            'no expiry on an access token' => $this->signJwt(array_diff_key($claims, ['exp' => true])),
            'a future nbf' => $this->signJwt(array_merge($claims, ['nbf' => $now->getTimestamp() + 120])),
            'a future iat' => $this->signJwt(array_merge($claims, ['iat' => $now->getTimestamp() + 120])),
        ];

        foreach ($cases as $case => $value) {
            $this->assertRejected(fn () => $this->verifier()->verify($value), $case);
        }

        $this->assertSame($claims, $this->verifier()->verify($this->signJwt($claims)));
    }

    /**
     * Test an expired token raises the expiry error with the code of its kind, after the leeway.
     */
    public function test_expiry_is_reported_per_kind_after_the_leeway(): void
    {
        $now = Carbon::now();

        $access = $this->signer()->signAccessToken(7, 12, $now, $now->copy()->addMinutes(15));
        $api = $this->signer()->signApiToken(3, 'abc123', 'erp.example.com', $now, $now->copy()->addMinutes(15));

        $this->travel(15 * 60 + 25)->seconds();

        $this->verifier()->verify($access);
        $this->verifier()->verify($api);

        $this->travel(10)->seconds();

        $this->assertExpired(fn () => $this->verifier()->verify($access), 'access_token_expired');
        $this->assertExpired(fn () => $this->verifier()->verify($api), 'api_token_expired');
    }

    // ========================================================================
    // Key Rotation
    // ========================================================================

    /**
     * Test a token signed by a previous key verifies while that public key is kept, and not after.
     */
    public function test_previous_public_keys_keep_old_tokens_verifying(): void
    {
        $now = Carbon::now();

        $oldKid = $this->fixtureKid();
        $old = $this->signer()->signApiToken(3, 'abc123', 'erp.example.com', $now, null);

        $newPrivate = $this->foreignPrivateKey();

        config([
            'bagistoapi.admin_auth.jwt.private_key' => $newPrivate,
            'bagistoapi.admin_auth.jwt.public_key' => null,
            'bagistoapi.admin_auth.jwt.previous_public_keys' => [$oldKid => $this->jwtFixturePublicKey()],
        ]);

        $this->forgetJwtServices();

        $fresh = $this->signer()->signApiToken(3, 'abc124', 'erp.example.com', $now, null);

        $this->assertNotSame($oldKid, $this->jwtHeader($fresh)['kid']);
        $this->assertSame('abc123', $this->verifier()->verify($old)['jti']);
        $this->assertSame('abc124', $this->verifier()->verify($fresh)['jti']);

        config(['bagistoapi.admin_auth.jwt.previous_public_keys' => []]);

        $this->forgetJwtServices();

        $this->assertRejected(fn () => $this->verifier()->verify($old));
        $this->assertSame('abc124', $this->verifier()->verify($fresh)['jti']);
    }

    /**
     * Test a key on another curve is refused.
     */
    public function test_a_key_on_another_curve_is_refused(): void
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp384r1',
        ]);

        openssl_pkey_export($key, $pem);

        config([
            'bagistoapi.admin_auth.jwt.private_key' => $pem,
            'bagistoapi.admin_auth.jwt.public_key' => null,
        ]);

        $this->forgetJwtServices();

        $this->expectException(\RuntimeException::class);

        $this->signer()->signAccessToken(7, 12, Carbon::now(), Carbon::now()->addMinutes(15));
    }

    /**
     * Sign with the test key pair under a fixed issuer.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://store.test']);

        $this->useFixtureJwtKeys();
    }

    /**
     * The JWT signer.
     */
    protected function signer(): JwtSigner
    {
        return app(JwtSigner::class);
    }

    /**
     * The JWT verifier.
     */
    protected function verifier(): JwtVerifier
    {
        return app(JwtVerifier::class);
    }

    /**
     * Drop the resolved key store, signer and verifier so they read the configuration again.
     */
    protected function forgetJwtServices(): void
    {
        $this->app->forgetInstance(JwtKeyStore::class);
        $this->app->forgetInstance(JwtSigner::class);
        $this->app->forgetInstance(JwtVerifier::class);
    }

    /**
     * Assert the callback rejects the token as invalid but not as expired.
     */
    protected function assertRejected(callable $callback, string $case = 'token'): void
    {
        try {
            $callback();
        } catch (ExpiredJwtException $exception) {
            $this->fail($case.' was reported as expired instead of invalid');
        } catch (InvalidJwtException $exception) {
            $this->assertSame('unauthenticated', $exception->errorCode(), $case);

            return;
        }

        $this->fail($case.' was accepted');
    }

    /**
     * Assert the callback reports the token as expired with the given code.
     */
    protected function assertExpired(callable $callback, string $code): void
    {
        try {
            $callback();
        } catch (ExpiredJwtException $exception) {
            $this->assertSame($code, $exception->errorCode());
            $this->assertSame(401, $exception->getStatusCode());

            return;
        }

        $this->fail('the token was not reported as expired');
    }
}
