<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Auth\AdminApiCredential;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AdminAuthException;
use Webkul\BagistoApi\Exception\AuthenticationException;

trait RequiresAdminSession
{
    /**
     * The credential of a request made with a login session's access token; an API token is refused.
     *
     * @throws AuthenticationException When the request is not authenticated (401).
     * @throws AdminAuthException When the request carries an API token (403).
     */
    protected function sessionCredential(): AdminApiCredential
    {
        $credential = AdminAuthHelper::credential();

        if ($credential === null) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if (! $credential->isSession()) {
            throw new AdminAuthException(
                __('bagistoapi::app.admin.auth.errors.access-token-required'),
                AdminAuthException::CODE_ACCESS_TOKEN_REQUIRED,
                403,
            );
        }

        return $credential;
    }
}
