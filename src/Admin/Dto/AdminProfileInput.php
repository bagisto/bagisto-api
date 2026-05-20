<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for updating the authenticated admin's own profile.
 *
 * Mirrors Bagisto core's admin AccountController: `currentPassword` is required
 * for any update; a password change additionally needs `password` +
 * `confirmPassword`.
 */
class AdminProfileInput
{
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[Groups(['mutation'])]
    public ?string $email = null;

    #[Groups(['mutation'])]
    public ?string $currentPassword = null;

    #[Groups(['mutation'])]
    public ?string $password = null;

    #[Groups(['mutation'])]
    public ?string $confirmPassword = null;
}
