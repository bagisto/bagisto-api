<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * Input for the exchange-rate auto-sync action. The action takes no required
 * input; this DTO only exists so the GraphQL mutation has an input type.
 */
class AdminSettingsExchangeRateUpdateRatesInput
{
    #[ApiProperty(description: 'Ignored placeholder so empty mutations validate.')]
    public ?bool $confirm = null;
}
