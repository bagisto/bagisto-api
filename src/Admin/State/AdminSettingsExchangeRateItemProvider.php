<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsExchangeRate;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Core\Models\CurrencyExchangeRate;

class AdminSettingsExchangeRateItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.exchange-rate.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return CurrencyExchangeRate::with('currency')->find($id);
    }

    protected function mapToDto(object $rate): AdminSettingsExchangeRate
    {
        /** @var CurrencyExchangeRate $rate */
        $dto = new AdminSettingsExchangeRate;

        $dto->id = (int) $rate->id;
        $dto->targetCurrency = $rate->target_currency !== null ? (int) $rate->target_currency : null;
        $dto->targetCurrencyCode = $rate->currency?->code;
        $dto->targetCurrencyName = $rate->currency?->name;
        $dto->rate = $rate->rate !== null ? (float) $rate->rate : null;
        $dto->createdAt = $rate->created_at?->toIso8601String();
        $dto->updatedAt = $rate->updated_at?->toIso8601String();

        return $dto;
    }
}
