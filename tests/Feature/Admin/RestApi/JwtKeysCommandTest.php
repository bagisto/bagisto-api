<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtKeyStore;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtVerifier;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class JwtKeysCommandTest extends AdminApiTestCase
{
    /**
     * The temporary key directory of the test.
     */
    protected string $keyDirectory;

    // ========================================================================
    // Creation
    // ========================================================================

    /**
     * Test the command creates a private key only the owner can read, a public key and a manifest.
     */
    public function test_the_command_creates_a_key_pair(): void
    {
        $this->artisan('bagisto-api:jwt-keys')->assertSuccessful();

        $kid = $this->manifest()['current'];

        $this->assertFileExists($this->keyDirectory.'/'.$kid.'.key');
        $this->assertFileExists($this->keyDirectory.'/'.$kid.'.pub');
        $this->assertSame('0600', $this->permissions($this->keyDirectory.'/'.$kid.'.key'));
        $this->assertSame('0700', $this->permissions($this->keyDirectory));
        $this->assertStringContainsString('BEGIN PUBLIC KEY', file_get_contents($this->keyDirectory.'/'.$kid.'.pub'));
    }

    /**
     * Test running it again keeps the existing key.
     */
    public function test_running_it_again_keeps_the_key(): void
    {
        $this->artisan('bagisto-api:jwt-keys')->assertSuccessful();

        $kid = $this->manifest()['current'];

        $this->artisan('bagisto-api:jwt-keys')
            ->expectsOutputToContain('already exist')
            ->assertSuccessful();

        $this->assertSame($kid, $this->manifest()['current']);
    }

    /**
     * Test keys from the configuration cannot be rotated by the command.
     */
    public function test_configured_keys_cannot_be_rotated(): void
    {
        $this->useFixtureJwtKeys();

        $this->artisan('bagisto-api:jwt-keys --rotate')->assertFailed();

        $this->assertDirectoryDoesNotExist($this->keyDirectory);
    }

    // ========================================================================
    // Rotation
    // ========================================================================

    /**
     * Test rotation signs new tokens with a new key and keeps old tokens verifying.
     */
    public function test_rotation_keeps_old_tokens_verifying(): void
    {
        $this->artisan('bagisto-api:jwt-keys')->assertSuccessful();

        $old = $this->manifest()['current'];

        $admin = $this->createAdmin();
        $oldToken = $this->adminAccessToken($admin);

        $this->artisan('bagisto-api:jwt-keys --rotate')->assertSuccessful();

        $manifest = $this->manifest();
        $new = $manifest['current'];

        $this->assertNotSame($old, $new);
        $this->assertNotNull($manifest['keys'][$old]['retired_at']);
        $this->assertFileDoesNotExist($this->keyDirectory.'/'.$old.'.key');
        $this->assertFileExists($this->keyDirectory.'/'.$old.'.pub');

        $newToken = $this->adminAccessToken($admin);

        $this->assertSame($new, $this->jwtHeader($newToken)['kid']);
        $this->withBearer('GET', '/api/admin/get', $oldToken)->assertOk();
        $this->withBearer('GET', '/api/admin/get', $newToken)->assertOk();
    }

    /**
     * Test API tokens signed by an older key are listed, and re-signed with --reissue-api-tokens.
     */
    public function test_api_tokens_signed_by_an_older_key_are_reissued(): void
    {
        $this->artisan('bagisto-api:jwt-keys')->assertSuccessful();

        $issued = $this->issueAdminApiToken($this->createAdmin());

        $this->artisan('bagisto-api:jwt-keys --rotate')
            ->expectsOutputToContain('signed by an older key')
            ->assertSuccessful();

        Artisan::call('bagisto-api:jwt-keys', ['--reissue-api-tokens' => true]);

        $this->assertSame(1, preg_match('/eyJ[\w-]+\.[\w-]+\.[\w-]+/', Artisan::output(), $match), 'no re-signed token was printed');

        $token = $issued->token->fresh();
        $current = $this->manifest()['current'];

        $this->assertSame($current, $token->signing_kid);
        $this->assertSame($current, $this->jwtHeader($match[0])['kid']);
        $this->assertSame(substr($match[0], -8), $token->token_preview);
        $this->assertSame($issued->token->jti, $this->jwtClaims($match[0])['jti']);

        $this->withBearer('GET', '/api/admin/get', $match[0])->assertOk();
    }

    // ========================================================================
    // Retired Keys
    // ========================================================================

    /**
     * Test the prune command drops a retired key after its grace period, unless an API token still uses it.
     */
    public function test_prune_drops_retired_keys_nothing_uses(): void
    {
        $this->artisan('bagisto-api:jwt-keys')->assertSuccessful();
        $unused = $this->manifest()['current'];

        $this->artisan('bagisto-api:jwt-keys --rotate')->assertSuccessful();
        $inUse = $this->manifest()['current'];

        $this->issueAdminApiToken($this->createAdmin());

        $this->artisan('bagisto-api:jwt-keys --rotate')->assertSuccessful();

        $this->artisan('bagisto-api:prune-admin-tokens')->assertSuccessful();

        $this->assertArrayHasKey($unused, $this->manifest()['keys'], 'a key was dropped while access tokens signed by it could still be valid');

        $this->travel(2)->hours();

        $this->artisan('bagisto-api:prune-admin-tokens')->assertSuccessful();

        $keys = $this->manifest()['keys'];

        $this->assertArrayNotHasKey($unused, $keys);
        $this->assertFileDoesNotExist($this->keyDirectory.'/'.$unused.'.pub');
        $this->assertArrayHasKey($inUse, $keys);
    }

    /**
     * Keep the keys in an empty temporary directory instead of the configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->keyDirectory = sys_get_temp_dir().'/bagisto-api-jwt-'.Str::random(12);

        config([
            'bagistoapi.admin_auth.jwt.private_key' => null,
            'bagistoapi.admin_auth.jwt.public_key' => null,
            'bagistoapi.admin_auth.jwt.path' => $this->keyDirectory,
        ]);

        $this->app->forgetInstance(JwtKeyStore::class);
        $this->app->forgetInstance(JwtSigner::class);
        $this->app->forgetInstance(JwtVerifier::class);
    }

    /**
     * Remove the temporary key directory.
     */
    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->keyDirectory);

        parent::tearDown();
    }

    /**
     * The key manifest.
     */
    protected function manifest(): array
    {
        return json_decode((string) file_get_contents($this->keyDirectory.'/'.JwtKeyStore::MANIFEST), true);
    }

    /**
     * The octal permissions of a path.
     */
    protected function permissions(string $path): string
    {
        clearstatcache();

        return substr(sprintf('%o', fileperms($path)), -4);
    }
}
