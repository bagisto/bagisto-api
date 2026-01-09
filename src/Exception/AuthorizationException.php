<?php

namespace Webkul\BagistoApi\Exception;

/**
 * AuthorizationException
 *
 * Thrown when an authenticated user tries to access a resource they don't have permission for.
 * This is different from AuthenticationException - the user IS authenticated but lacks permissions.
 *
 * Examples:
 * - User trying to access another user's cart
 * - User trying to modify another user's order
 * - User trying to access resources outside their scope
 *
 * Status Code: 403 Forbidden
 */
class AuthorizationException extends \Exception implements \GraphQL\Error\ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'authorization';
    }
}
