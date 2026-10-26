<?php

namespace Webkul\BagistoApi\Admin\Services\Jwt;

use Carbon\CarbonInterface;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthSettings;

class JwtSigner
{
    /**
     * The only signature algorithm admin API tokens are signed and verified with.
     */
    public const ALGORITHM = 'ES256';

    /**
     * The `typ` claim of an access token.
     */
    public const TYPE_ACCESS = 'access';

    /**
     * The `typ` claim of an API token.
     */
    public const TYPE_API = 'api';

    /**
     * The `typ` claim of a storefront customer access token.
     */
    public const TYPE_CUSTOMER_ACCESS = 'customer_access';

    /**
     * Create a new signer.
     */
    public function __construct(protected JwtKeyStore $keys) {}

    /**
     * Sign an access token for a session.
     */
    public function signAccessToken(int $adminId, int $sessionId, CarbonInterface $issuedAt, CarbonInterface $expiresAt): string
    {
        return $this->sign([
            'iss' => $this->issuer(),
            'aud' => $this->audience(),
            'sub' => $adminId,
            'typ' => self::TYPE_ACCESS,
            'sid' => $sessionId,
            'jti' => bin2hex(random_bytes(16)),
            'iat' => $issuedAt->getTimestamp(),
            'nbf' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ]);
    }

    /**
     * The longest lifetime any access token can have, in minutes, so a retired key outlives every token it signed.
     */
    public static function longestAccessTokenMinutes(): int
    {
        return max(AdminApiAuthSettings::ACCESS_TOKEN_TTL_MAX, CustomerApiAuthSettings::ACCESS_TOKEN_TTL_MAX);
    }

    /**
     * Sign a storefront customer access token for a session.
     */
    public function signCustomerAccessToken(int $customerId, int $sessionId, CarbonInterface $issuedAt, CarbonInterface $expiresAt): string
    {
        return $this->sign([
            'iss' => $this->issuer(),
            'aud' => $this->customerAudience(),
            'sub' => $customerId,
            'typ' => self::TYPE_CUSTOMER_ACCESS,
            'sid' => $sessionId,
            'jti' => bin2hex(random_bytes(16)),
            'iat' => $issuedAt->getTimestamp(),
            'nbf' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ]);
    }

    /**
     * Sign an API token bound to a domain, without `exp` when it never expires.
     */
    public function signApiToken(int $adminId, string $jti, string $domain, CarbonInterface $issuedAt, ?CarbonInterface $expiresAt): string
    {
        $claims = [
            'iss' => $this->issuer(),
            'aud' => $domain,
            'sub' => $adminId,
            'typ' => self::TYPE_API,
            'jti' => $jti,
            'iat' => $issuedAt->getTimestamp(),
            'nbf' => $issuedAt->getTimestamp(),
        ];

        if ($expiresAt !== null) {
            $claims['exp'] = $expiresAt->getTimestamp();
        }

        return $this->sign($claims);
    }

    /**
     * Sign a set of claims with the current signing key.
     */
    public function sign(array $claims): string
    {
        $kid = $this->keys->signingKid();

        $jws = (new JWSBuilder(new AlgorithmManager([new ES256])))
            ->create()
            ->withPayload(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))
            ->addSignature($this->keys->signingKey(), [
                'alg' => self::ALGORITHM,
                'typ' => 'JWT',
                'kid' => $kid,
            ])
            ->build();

        return (new CompactSerializer)->serialize($jws, 0);
    }

    /**
     * The id of the key tokens are currently signed with.
     */
    public function signingKid(): string
    {
        return $this->keys->signingKid();
    }

    /**
     * The `iss` claim: the store's URL.
     */
    public function issuer(): string
    {
        return (string) config('app.url');
    }

    /**
     * The `aud` claim of an access token.
     */
    public function audience(): string
    {
        return (string) config('bagistoapi.admin_auth.jwt.audience', 'bagisto-admin-api');
    }

    /**
     * The `aud` claim of a storefront customer access token.
     */
    public function customerAudience(): string
    {
        return (string) config('bagistoapi.customer_auth.jwt.audience', 'bagisto-shop-api');
    }
}
