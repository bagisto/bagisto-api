<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;

class AdminSettingsExchangeRateUpdateRatesInput
{
    /**
     * The confirm.
     */
    #[ApiProperty(description: 'Ignored placeholder so empty mutations validate.')]
    public ?bool $confirm = null;
}
