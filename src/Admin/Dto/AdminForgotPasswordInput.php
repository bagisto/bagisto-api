<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for the admin forgot-password request.
 */
class AdminForgotPasswordInput
{
    #[Groups(['mutation'])]
    public string $email;

    public function __construct(string $email = '')
    {
        $this->email = $email;
    }
}
