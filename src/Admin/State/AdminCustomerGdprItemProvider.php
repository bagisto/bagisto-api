<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprRequest;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\GDPR\Models\GDPRDataRequest;

class AdminCustomerGdprItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.gdpr.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return GDPRDataRequest::with(['customer'])->find($id);
    }

    public function mapToDto(object $entity): object
    {
        return $this->doMap($entity);
    }

    protected function doMap(GDPRDataRequest $r): AdminCustomerGdprRequest
    {
        $dto = new AdminCustomerGdprRequest;
        $dto->id = (int) $r->id;
        $dto->customerId = $r->customer_id !== null ? (int) $r->customer_id : null;
        $dto->customerName = $r->customer
            ? trim((string) $r->customer->first_name.' '.(string) $r->customer->last_name)
            : null;
        $dto->email = $r->email;
        $dto->type = $r->type;
        $dto->status = $r->status;
        $dto->message = $r->message;
        $dto->revokedAt = $r->revoked_at?->toIso8601String();
        $dto->createdAt = $r->created_at?->toIso8601String();
        $dto->updatedAt = $r->updated_at?->toIso8601String();

        return $dto;
    }
}
