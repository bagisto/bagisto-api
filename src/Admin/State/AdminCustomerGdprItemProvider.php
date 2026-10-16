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

    protected function doMap(GDPRDataRequest $gDPRDataRequest): AdminCustomerGdprRequest
    {
        $dto = new AdminCustomerGdprRequest;
        $dto->id = (int) $gDPRDataRequest->id;
        $dto->customerId = $gDPRDataRequest->customer_id !== null ? (int) $gDPRDataRequest->customer_id : null;
        $dto->customerName = $gDPRDataRequest->customer
            ? trim((string) $gDPRDataRequest->customer->first_name.' '.(string) $gDPRDataRequest->customer->last_name)
            : null;
        $dto->email = $gDPRDataRequest->email;
        $dto->type = $gDPRDataRequest->type;
        $dto->status = $gDPRDataRequest->status;
        $dto->message = $gDPRDataRequest->message;
        $dto->revokedAt = $gDPRDataRequest->revoked_at?->toIso8601String();
        $dto->createdAt = $gDPRDataRequest->created_at?->toIso8601String();
        $dto->updatedAt = $gDPRDataRequest->updated_at?->toIso8601String();

        return $dto;
    }
}
