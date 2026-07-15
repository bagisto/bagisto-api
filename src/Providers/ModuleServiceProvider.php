<?php

namespace Webkul\BagistoApi\Providers;

use Webkul\BagistoApi\Models\GuestCartTokens;
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
        GuestCartTokens::class,
    ];
}
