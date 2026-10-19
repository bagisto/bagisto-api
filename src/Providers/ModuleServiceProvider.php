<?php

namespace Webkul\BagistoApi\Providers;

use Webkul\BagistoApi\Models\GuestCartTokens;
use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        GuestCartTokens::class,
    ];

    /**
     * Boot the package services.
     */
    public function boot(): void
    {
        parent::boot();

    }
}
