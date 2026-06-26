<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Exception\InvalidInputException;

trait GdprFeatureGate
{
    protected function assertGdprEnabled(): void
    {
        if (! core()->getConfigData('general.gdpr.settings.enabled')) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.disabled'));
        }
    }
}
