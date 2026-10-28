<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class SocialLoginInput
{
    #[Groups(['mutation'])]
    public ?string $provider = null;

    #[Groups(['mutation'])]
    public ?string $idToken = null;

    #[Groups(['mutation'])]
    public ?string $accessToken = null;

    #[Groups(['mutation'])]
    public ?string $deviceToken = null;

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
