<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Models\EuWithdrawal;
use Webkul\BagistoApi\Models\GuestEuWithdrawal;
use Webkul\EUWithdrawal\Models\Withdrawal;

trait BuildsEuWithdrawal
{
    private function fillEuWithdrawal(object $dto, Withdrawal $w): object
    {
        $dto->id                   = $w->id;
        $dto->uuid                 = $w->uuid;
        $dto->order_id             = $w->order_id;
        $dto->order_increment_id   = $w->order?->increment_id;
        $dto->is_guest             = (bool) $w->is_guest;
        $dto->customer_email       = $w->customer_email;
        $dto->status               = $w->status;
        $dto->reason_text          = $w->reason_text;
        $dto->received_at          = $w->received_at?->toIso8601String();
        $dto->confirmation_sent_at = $w->confirmation_sent_at?->toIso8601String();
        $dto->created_at           = $w->created_at?->toIso8601String();

        return $dto;
    }

    private function buildEuWithdrawal(Withdrawal $w): EuWithdrawal
    {
        /** @var EuWithdrawal $dto */
        $dto = $this->fillEuWithdrawal(new EuWithdrawal, $w);

        $dto->updated_at      = $w->updated_at?->toIso8601String();
        $dto->declined_at     = $w->declined_at?->toIso8601String();
        $dto->declined_reason = $w->declined_reason;
        $dto->refunded_at     = $w->refunded_at?->toIso8601String();
        $dto->refund_note     = $w->refund_note;

        return $dto;
    }

    private function buildGuestEuWithdrawal(Withdrawal $w): GuestEuWithdrawal
    {
        /** @var GuestEuWithdrawal $dto */
        return $this->fillEuWithdrawal(new GuestEuWithdrawal, $w);
    }
}
