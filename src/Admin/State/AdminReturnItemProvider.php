<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Webkul\BagistoApi\Admin\Models\AdminReturn;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminReturn;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\RMA\Repositories\RMAStatusRepository;

class AdminReturnItemProvider extends AbstractAdminItemProvider
{
    use BuildsAdminReturn;
    use ChecksAdminPermission;

    public function __construct(
        private readonly RMARepository $rmaRepository,
        private readonly RMAStatusRepository $rmaStatusRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin('sales.rma.requests');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.rma.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return $this->rmaRepository->with(self::RETURN_RELATIONS)->find($id);
    }

    protected function mapToDto(object $entity): AdminReturn
    {
        return $this->buildAdminReturn($entity, $this->rmaRepository, $this->rmaStatusRepository);
    }
}
