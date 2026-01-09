<?php

namespace Webkul\BagistoApi\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    public function boot(): void
    {
        parent::boot();

    }

    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Webkul\BagistoApi\Models\GuestCartTokens::class,
    ];
}
