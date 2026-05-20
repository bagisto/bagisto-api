<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for admin logout.
 *
 * `all = true` revokes every token for the admin (logout from all devices);
 * otherwise only the current request's token is revoked.
 */
class AdminLogoutInput
{
    #[Groups(['mutation'])]
    public ?bool $all = false;

    public function __construct(?bool $all = false)
    {
        $this->all = $all;
    }
}
