<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminEuWithdrawal;

trait BuildsAdminEuWithdrawal
{
    protected function baseWithdrawalQuery()
    {
        return DB::table('eu_withdrawals as w')
            ->leftJoin('orders as o', 'w.order_id', '=', 'o.id')
            ->leftJoin('channels as c', 'w.channel_id', '=', 'c.id')
            ->leftJoin('customers as cust', 'w.customer_id', '=', 'cust.id')
            ->leftJoin('admins as da', 'w.declined_by_user_id', '=', 'da.id')
            ->leftJoin('admins as ra', 'w.refunded_by_user_id', '=', 'ra.id')
            ->select(
                'w.id', 'w.uuid', 'w.order_id', 'w.customer_id', 'w.is_guest', 'w.customer_email',
                'w.channel_id', 'w.locale', 'w.reason_text', 'w.status', 'w.received_at',
                'w.confirmation_sent_at', 'w.final_confirmation_sent_at', 'w.confirmation_error',
                'w.declined_at', 'w.declined_reason', 'w.declined_by_user_id',
                'w.refunded_at', 'w.refunded_by_user_id', 'w.refund_note',
                'w.created_at', 'w.updated_at',
                'o.increment_id as order_increment_id',
                'c.code as channel_code',
                'cust.first_name as customer_first_name',
                'cust.last_name as customer_last_name',
                'da.name as declined_by_name',
                'ra.name as refunded_by_name',
            );
    }

    protected function mapWithdrawalRow(object $row): AdminEuWithdrawal
    {
        $dto = new AdminEuWithdrawal;

        $dto->id                         = (int) $row->id;
        $dto->uuid                       = $row->uuid;
        $dto->order_id                   = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->order_increment_id         = $row->order_increment_id;
        $dto->customer_id                = $row->customer_id !== null ? (int) $row->customer_id : null;
        $dto->customer_name              = $this->joinName($row->customer_first_name ?? null, $row->customer_last_name ?? null);
        $dto->customer_email             = $row->customer_email;
        $dto->is_guest                   = (bool) $row->is_guest;
        $dto->channel_id                 = $row->channel_id !== null ? (int) $row->channel_id : null;
        $dto->channel_code               = $row->channel_code;
        $dto->locale                     = $row->locale;
        $dto->reason_text                = $row->reason_text;
        $dto->status                     = $row->status;
        $dto->received_at                = $this->iso($row->received_at);
        $dto->confirmation_sent_at       = $this->iso($row->confirmation_sent_at);
        $dto->final_confirmation_sent_at = $this->iso($row->final_confirmation_sent_at);
        $dto->confirmation_error         = $row->confirmation_error;
        $dto->declined_at                = $this->iso($row->declined_at);
        $dto->declined_reason            = $row->declined_reason;
        $dto->declined_by_user_id        = $row->declined_by_user_id !== null ? (int) $row->declined_by_user_id : null;
        $dto->declined_by_name           = $row->declined_by_name;
        $dto->refunded_at                = $this->iso($row->refunded_at);
        $dto->refunded_by_user_id        = $row->refunded_by_user_id !== null ? (int) $row->refunded_by_user_id : null;
        $dto->refunded_by_name           = $row->refunded_by_name;
        $dto->refund_note                = $row->refund_note;
        $dto->created_at                 = $this->iso($row->created_at);
        $dto->updated_at                 = $this->iso($row->updated_at);

        return $dto;
    }

    private function iso(?string $value): ?string
    {
        return $value ? Carbon::parse($value)->toIso8601String() : null;
    }

    private function joinName(?string $first, ?string $last): ?string
    {
        $name = trim(($first ?? '').' '.($last ?? ''));

        return $name !== '' ? $name : null;
    }
}
