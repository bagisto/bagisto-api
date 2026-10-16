<?php

namespace Webkul\BagistoApi\Providers\Concerns;

use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use Webkul\BagistoApi\Admin\State\AdminReturnMessageProcessor;
use Webkul\BagistoApi\Admin\State\AdminReturnProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusProcessor;
use Webkul\RMA\Repositories\RMAAdditionalFieldRepository;
use Webkul\RMA\Repositories\RMACustomFieldOptionRepository;
use Webkul\RMA\Repositories\RMACustomFieldRepository;
use Webkul\RMA\Repositories\RMAImageRepository;
use Webkul\RMA\Repositories\RMAItemRepository;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMAReasonRepository;
use Webkul\RMA\Repositories\RMAReasonResolutionRepository;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\RMA\Repositories\RMARuleRepository;
use Webkul\RMA\Repositories\RMAStatusRepository;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\RefundRepository;

trait RegistersAdminProcessors
{
    /**
     * Register the admin returns and RMA processors.
     */
    protected function registerAdminReturnProcessors(): void
    {
        $this->app->singleton(AdminReturnMessageProcessor::class, function ($app) {
            return new AdminReturnMessageProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARepository::class),
                $app->make(RMAMessageRepository::class),
            );
        });

        $this->app->singleton(AdminRmaReasonProcessor::class, function ($app) {
            return new AdminRmaReasonProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMAReasonRepository::class),
                $app->make(RMAReasonResolutionRepository::class),
            );
        });

        $this->app->singleton(AdminRmaStatusProcessor::class, function ($app) {
            return new AdminRmaStatusProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMAStatusRepository::class),
            );
        });

        $this->app->singleton(AdminRmaRuleProcessor::class, function ($app) {
            return new AdminRmaRuleProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARuleRepository::class),
            );
        });

        $this->app->singleton(AdminRmaCustomFieldProcessor::class, function ($app) {
            return new AdminRmaCustomFieldProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMACustomFieldRepository::class),
                $app->make(RMACustomFieldOptionRepository::class),
            );
        });

        $this->app->singleton(AdminReturnProcessor::class, function ($app) {
            return new AdminReturnProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARepository::class),
                $app->make(RMAItemRepository::class),
                $app->make(RMAImageRepository::class),
                $app->make(RMAMessageRepository::class),
                $app->make(RMAAdditionalFieldRepository::class),
                $app->make(RMAStatusRepository::class),
                $app->make(OrderItemRepository::class),
                $app->make(OrderRepository::class),
                $app->make(RefundRepository::class),
            );
        });
    }
}
