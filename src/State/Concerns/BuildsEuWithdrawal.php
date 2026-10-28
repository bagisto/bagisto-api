<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Models\EuWithdrawal;
use Webkul\BagistoApi\Models\GuestEuWithdrawal;
use Webkul\EUWithdrawal\Models\Withdrawal;

trait BuildsEuWithdrawal
{
    /**
     * Fill eu withdrawal.
     */
    private function fillEuWithdrawal(object $dto, Withdrawal $withdrawal): object
    {
        $dto->id = $withdrawal->id;
        $dto->uuid = $withdrawal->uuid;
        $dto->order_id = $withdrawal->order_id;
        $dto->order_increment_id = $withdrawal->order?->increment_id;
        $dto->is_guest = (bool) $withdrawal->is_guest;
        $dto->customer_email = $withdrawal->customer_email;
        $dto->status = $withdrawal->status;
        $dto->reason_text = $withdrawal->reason_text;
        $dto->received_at = $withdrawal->received_at?->toIso8601String();
        $dto->confirmation_sent_at = $withdrawal->confirmation_sent_at?->toIso8601String();
        $dto->created_at = $withdrawal->created_at?->toIso8601String();

        return $dto;
    }

    /**
     * Build eu withdrawal.
     */
    private function buildEuWithdrawal(Withdrawal $withdrawal): EuWithdrawal
    {
        $dto = $this->fillEuWithdrawal(new EuWithdrawal, $withdrawal);

        $dto->updated_at = $withdrawal->updated_at?->toIso8601String();
        $dto->declined_at = $withdrawal->declined_at?->toIso8601String();
        $dto->declined_reason = $withdrawal->declined_reason;
        $dto->refunded_at = $withdrawal->refunded_at?->toIso8601String();
        $dto->refund_note = $withdrawal->refund_note;

        return $dto;
    }

    /**
     * Build guest eu withdrawal.
     */
    private function buildGuestEuWithdrawal(Withdrawal $withdrawal): GuestEuWithdrawal
    {
        return $this->fillEuWithdrawal(new GuestEuWithdrawal, $withdrawal);
    }
}
