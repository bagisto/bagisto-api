<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Webkul\BagistoApi\Admin\Models\AdminEuWithdrawal;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminEuWithdrawal;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminEuWithdrawalItemProvider extends AbstractAdminItemProvider
{
    use BuildsAdminEuWithdrawal;
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin('sales.eu_withdrawals.view', 'bagistoapi::app.admin.eu-withdrawal.no-permission');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.eu-withdrawal.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return $this->baseWithdrawalQuery()->where('w.id', $id)->first();
    }

    protected function mapToDto(object $entity): AdminEuWithdrawal
    {
        return $this->mapWithdrawalRow($entity);
    }
}
