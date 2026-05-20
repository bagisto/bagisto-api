<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for admin login (REST + GraphQL).
 */
class AdminLoginInput
{
    #[Groups(['mutation'])]
    public string $email;

    #[Groups(['mutation'])]
    public string $password;

    public function __construct(string $email = '', string $password = '')
    {
        $this->email = $email;
        $this->password = $password;
    }
}
