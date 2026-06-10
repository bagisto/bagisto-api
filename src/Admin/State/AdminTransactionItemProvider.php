<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminTransaction;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminTransaction;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminTransactionItemProvider extends AbstractAdminItemProvider
{
    use BuildsAdminTransaction;
    use ChecksAdminPermission;

    protected const PERMISSION = 'sales.transactions.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.sales.transaction.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        $row = DB::table('order_transactions')
            ->leftJoin('orders', 'order_transactions.order_id', '=', 'orders.id')
            ->where('order_transactions.id', $id)
            ->select($this->adminTransactionSelect())
            ->first();

        return $row ?: null;
    }

    protected function mapToDto(object $entity): AdminTransaction
    {
        return $this->buildAdminTransaction($entity);
    }
}
