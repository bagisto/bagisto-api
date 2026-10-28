<?php

namespace Webkul\BagistoApi\Admin\Services\Jwt;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use RuntimeException;

class JwtKeyStore
{
    /**
     * The curve every signing key must use.
     */
    public const CURVE = 'P-256';

    /**
     * The file that records the signing key id and the retired keys.
     */
    public const MANIFEST = 'keys.json';

    /**
     * The parsed public keys, keyed by key id.
     */
    protected array $publicKeys = [];

    /**
     * The parsed signing key.
     */
    protected ?JWK $signingKey = null;

    /**
     * Create a new key store.
     */
    public function __construct(protected Filesystem $files) {}

    /**
     * Whether the keys come from the configuration rather than the storage directory.
     */
    public function usesConfiguredKeys(): bool
    {
        return filled(config('bagistoapi.admin_auth.jwt.private_key'));
    }

    /**
     * Whether a signing key is available.
     */
    public function exists(): bool
    {
        if ($this->usesConfiguredKeys()) {
            return true;
        }

        $kid = $this->manifest()['current'] ?? null;

        return $kid !== null
            && $this->files->exists($this->privateKeyPath($kid));
    }

    /**
     * Create the first key pair in the storage directory and return its key id.
     */
    public function create(): string
    {
        if ($this->exists()) {
            throw new RuntimeException('JWT signing keys already exist.');
        }

        $kid = $this->storePair();

        $this->writeManifest([
            'current' => $kid,
            'keys' => [$kid => ['created_at' => Carbon::now()->toIso8601String(), 'retired_at' => null]],
        ]);

        return $kid;
    }

    /**
     * Make a new key pair the signing key, keep the old public key for verification and return the new key id.
     */
    public function rotate(): string
    {
        if ($this->usesConfiguredKeys()) {
            throw new RuntimeException('JWT keys are provided by the configuration and cannot be rotated here.');
        }

        if (! $this->exists()) {
            return $this->create();
        }

        $manifest = $this->manifest();

        $previous = $manifest['current'];

        $kid = $this->storePair();

        $manifest['keys'][$previous]['retired_at'] = Carbon::now()->toIso8601String();
        $manifest['keys'][$kid] = ['created_at' => Carbon::now()->toIso8601String(), 'retired_at' => null];
        $manifest['current'] = $kid;

        $this->writeManifest($manifest);

        $this->files->delete($this->privateKeyPath($previous));

        $this->signingKey = null;

        return $kid;
    }

    /**
     * The id of the key new tokens are signed with.
     */
    public function signingKid(): string
    {
        if ($this->usesConfiguredKeys()) {
            return filled(config('bagistoapi.admin_auth.jwt.kid'))
                ? (string) config('bagistoapi.admin_auth.jwt.kid')
                : $this->thumbprint($this->configuredPublicKey());
        }

        $kid = $this->manifest()['current'] ?? null;

        if ($kid === null) {
            throw new RuntimeException('No JWT signing key exists. Run `php artisan bagisto-api:jwt-keys`.');
        }

        return $kid;
    }

    /**
     * The private key new tokens are signed with.
     */
    public function signingKey(): JWK
    {
        if ($this->signingKey) {
            return $this->signingKey;
        }

        $pem = $this->usesConfiguredKeys()
            ? $this->decodePem((string) config('bagistoapi.admin_auth.jwt.private_key'))
            : $this->read($this->privateKeyPath($this->signingKid()));

        return $this->signingKey = $this->toJwk($pem, $this->signingKid());
    }

    /**
     * The public key with the given id, or null when no such key is kept.
     */
    public function verificationKey(string $kid): ?JWK
    {
        if (array_key_exists($kid, $this->publicKeys)) {
            return $this->publicKeys[$kid];
        }

        $pem = $this->publicKeyPem($kid);

        return $this->publicKeys[$kid] = $pem === null
            ? null
            : $this->toJwk($pem, $kid);
    }

    /**
     * The retired key ids kept for verification, with the moment each was retired.
     */
    public function retiredKeys(): array
    {
        if ($this->usesConfiguredKeys()) {
            return [];
        }

        return collect($this->manifest()['keys'] ?? [])
            ->filter(fn (array $key) => $key['retired_at'] !== null)
            ->map(fn (array $key) => Carbon::parse($key['retired_at']))
            ->all();
    }

    /**
     * Drop a retired public key, so tokens signed by it stop verifying.
     */
    public function forget(string $kid): void
    {
        $manifest = $this->manifest();

        if (
            ($manifest['current'] ?? null) === $kid
            || ! isset($manifest['keys'][$kid])
        ) {
            return;
        }

        unset($manifest['keys'][$kid], $this->publicKeys[$kid]);

        $this->writeManifest($manifest);

        $this->files->delete($this->publicKeyPath($kid));
    }

    /**
     * The directory the key files are stored in.
     */
    public function directory(): string
    {
        return rtrim((string) config('bagistoapi.admin_auth.jwt.path'), '/');
    }

    /**
     * The public key PEM with the given id, from the configuration or the storage directory.
     */
    protected function publicKeyPem(string $kid): ?string
    {
        if ($this->usesConfiguredKeys()) {
            if ($kid === $this->signingKid()) {
                return $this->configuredPublicKey();
            }

            $previous = (array) config('bagistoapi.admin_auth.jwt.previous_public_keys', []);

            return isset($previous[$kid])
                ? $this->decodePem((string) $previous[$kid])
                : null;
        }

        if (! isset($this->manifest()['keys'][$kid])) {
            return null;
        }

        $path = $this->publicKeyPath($kid);

        return $this->files->exists($path)
            ? $this->read($path)
            : null;
    }

    /**
     * The configured public key, or the one derived from the configured private key.
     */
    protected function configuredPublicKey(): string
    {
        if (filled(config('bagistoapi.admin_auth.jwt.public_key'))) {
            return $this->decodePem((string) config('bagistoapi.admin_auth.jwt.public_key'));
        }

        $private = openssl_pkey_get_private($this->decodePem((string) config('bagistoapi.admin_auth.jwt.private_key')));

        if ($private === false) {
            throw new RuntimeException('The configured JWT private key cannot be read.');
        }

        return openssl_pkey_get_details($private)['key'];
    }

    /**
     * Generate an ES256 key pair, write it to the storage directory and return its key id.
     */
    protected function storePair(): string
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);

        if (
            $key === false
            || ! openssl_pkey_export($key, $privatePem)
        ) {
            throw new RuntimeException('Unable to generate an ES256 key pair: '.openssl_error_string());
        }

        $publicPem = openssl_pkey_get_details($key)['key'];

        $kid = $this->thumbprint($publicPem);

        $this->files->ensureDirectoryExists($this->directory(), 0700);

        $this->files->put($this->privateKeyPath($kid), $privatePem);
        $this->files->chmod($this->privateKeyPath($kid), 0600);

        $this->files->put($this->publicKeyPath($kid), $publicPem);
        $this->files->chmod($this->publicKeyPath($kid), 0644);

        return $kid;
    }

    /**
     * The manifest of the storage directory.
     */
    protected function manifest(): array
    {
        $path = $this->directory().'/'.self::MANIFEST;

        if (! $this->files->exists($path)) {
            return [];
        }

        return json_decode($this->files->get($path), true) ?: [];
    }

    /**
     * Write the manifest of the storage directory.
     */
    protected function writeManifest(array $manifest): void
    {
        $this->files->ensureDirectoryExists($this->directory(), 0700);

        $this->files->replace(
            $this->directory().'/'.self::MANIFEST,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            0600,
        );
    }

    /**
     * Read a key file.
     */
    protected function read(string $path): string
    {
        if (! $this->files->exists($path)) {
            throw new RuntimeException("The JWT key file {$path} does not exist.");
        }

        return $this->files->get($path);
    }

    /**
     * Turn a PEM key into a JWK usable only for ES256 signatures.
     */
    protected function toJwk(string $pem, string $kid): JWK
    {
        $jwk = JWKFactory::createFromKey($pem, null, [
            'kid' => $kid,
            'alg' => JwtSigner::ALGORITHM,
            'use' => 'sig',
        ]);

        if (
            $jwk->get('kty') !== 'EC'
            || $jwk->get('crv') !== self::CURVE
        ) {
            throw new RuntimeException('JWT keys must be EC keys on the P-256 curve.');
        }

        return $jwk;
    }

    /**
     * The key id of a public key: its RFC 7638 SHA-256 thumbprint.
     */
    protected function thumbprint(string $publicPem): string
    {
        return JWKFactory::createFromKey($publicPem)->thumbprint('sha256');
    }

    /**
     * Accept a configured key as PEM or as base64-encoded PEM.
     */
    protected function decodePem(string $value): string
    {
        $value = trim(str_replace('\n', "\n", $value));

        if (str_starts_with($value, '-----BEGIN')) {
            return $value;
        }

        return (string) base64_decode($value, true);
    }

    /**
     * The path of a private key file.
     */
    protected function privateKeyPath(string $kid): string
    {
        return $this->directory().'/'.$kid.'.key';
    }

    /**
     * The path of a public key file.
     */
    protected function publicKeyPath(string $kid): string
    {
        return $this->directory().'/'.$kid.'.pub';
    }
}
