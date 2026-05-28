<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsCurrency;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Core\Models\Currency;

class AdminSettingsCurrencyItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.currency.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Currency::find($id);
    }

    protected function mapToDto(object $currency): AdminSettingsCurrency
    {
        /** @var Currency $currency */
        $dto = new AdminSettingsCurrency;

        $dto->id = (int) $currency->id;
        $dto->code = $currency->code;
        $dto->name = $currency->name;
        $dto->symbol = $currency->symbol;
        $dto->decimal = $currency->decimal !== null ? (int) $currency->decimal : null;
        $dto->groupSeparator = $currency->group_separator;
        $dto->decimalSeparator = $currency->decimal_separator;
        $dto->currencyPosition = $currency->currency_position;
        $dto->createdAt = $currency->created_at?->toIso8601String();
        $dto->updatedAt = $currency->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * Public alias used by the processor to share the mapping logic.
     */
    public function mapToDtoPublic(object $currency): AdminSettingsCurrency
    {
        return $this->mapToDto($currency);
    }
}
