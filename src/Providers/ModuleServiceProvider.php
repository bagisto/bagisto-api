<?php

namespace Webkul\BagistoApi\Providers;

use Webkul\BagistoApi\Admin\Models\AdminApiRefreshToken;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Models\GuestCartTokens;
use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * The models registered with Concord.
     *
     * @var array
     */
    protected $models = [
        GuestCartTokens::class,
        AdminApiSession::class,
        AdminApiRefreshToken::class,
        AdminApiToken::class,
    ];

    /**
     * Boot the package services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
