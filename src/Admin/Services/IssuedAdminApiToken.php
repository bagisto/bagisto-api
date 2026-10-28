<?php

namespace Webkul\BagistoApi\Admin\Services;

use Webkul\BagistoApi\Admin\Models\AdminApiToken;

class IssuedAdminApiToken
{
    /**
     * Create the result of issuing an API token: the stored row and the token string, shown only once.
     */
    public function __construct(
        public readonly AdminApiToken $token,
        public readonly string $plainText,
    ) {}
}
