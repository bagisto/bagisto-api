<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\EuWithdrawalStatus;
use Webkul\BagistoApi\Models\GdprStatus;

class FeatureStatusProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [self::build($operation->getClass())];
    }

    /**
     * Build.
     */
    public static function build(string $class): GdprStatus|EuWithdrawalStatus
    {
        $channel = core()->getCurrentChannel();

        $status = new $class;

        $status->id = $channel->code;
        $status->channel = $channel->code;
        $status->enabled = match ($class) {
            GdprStatus::class => (bool) core()->getConfigData('general.gdpr.settings.enabled'),
            EuWithdrawalStatus::class => (bool) core()->getConfigData('sales.eu_withdrawal.general.enabled', $channel->code),
        };

        return $status;
    }
}
