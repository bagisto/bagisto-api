<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class SocialLoginInput
{
    /**
     * The provider.
     */
    #[Groups(['mutation'])]
    public ?string $provider = null;

    /**
     * The id token.
     */
    #[Groups(['mutation'])]
    public ?string $idToken = null;

    /**
     * The access token.
     */
    #[Groups(['mutation'])]
    public ?string $accessToken = null;

    /**
     * The device token.
     */
    #[Groups(['mutation'])]
    public ?string $deviceToken = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        ?string $provider = null,
        ?string $idToken = null,
        ?string $accessToken = null,
        ?string $deviceToken = null,
    ) {
        $this->provider = $provider;
        $this->idToken = $idToken;
        $this->accessToken = $accessToken;
        $this->deviceToken = $deviceToken;
    }
}
