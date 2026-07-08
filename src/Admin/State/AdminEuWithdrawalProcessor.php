<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminEuWithdrawalActionInput;
use Webkul\BagistoApi\Admin\Dto\AdminEuWithdrawalDeclineInput;
use Webkul\BagistoApi\Admin\Dto\AdminEuWithdrawalMarkRefundedInput;
use Webkul\BagistoApi\Admin\Models\AdminEuWithdrawal;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminEuWithdrawal;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\EUWithdrawal\Enums\WithdrawalStatus;
use Webkul\EUWithdrawal\Repositories\WithdrawalRepository;
use Webkul\Shop\Mail\Customer\EUWithdrawal\WithdrawalConfirmation;

class AdminEuWithdrawalProcessor implements ProcessorInterface
{
    use BuildsAdminEuWithdrawal;
    use ChecksAdminPermission;

    public function __construct(private readonly WithdrawalRepository $withdrawals) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminEuWithdrawal
    {
        $id = $this->resolveId($data, $uriVariables);

        if ($data instanceof AdminEuWithdrawalDeclineInput) {
            return $this->handleDecline($id, $data->declined_reason);
        }

        if ($data instanceof AdminEuWithdrawalMarkRefundedInput) {
            return $this->handleMarkRefunded($id, $data->refund_note);
        }

        if ($data instanceof AdminEuWithdrawalActionInput) {
            return $this->handleResend($id);
        }

        throw new InvalidInputException(__('bagistoapi::app.admin.eu-withdrawal.not-found'), 422);
    }

    private function handleDecline(int $id, ?string $reason): AdminEuWithdrawal
    {
        $admin = $this->authorizedAdmin('sales.eu_withdrawals.decline', 'bagistoapi::app.admin.eu-withdrawal.no-permission');

        $withdrawal = $this->findOrFail($id);

        $validator = Validator::make(['declined_reason' => $reason], [
            'declined_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $withdrawal->update([
            'status'              => WithdrawalStatus::DECLINED,
            'declined_at'         => now(),
            'declined_reason'     => $reason,
            'declined_by_user_id' => $admin->id,
            'refunded_at'         => null,
            'refunded_by_user_id' => null,
            'refund_note'         => null,
        ]);

        return $this->result($id, __('bagistoapi::app.admin.eu-withdrawal.declined'));
    }

    private function handleMarkRefunded(int $id, ?string $note): AdminEuWithdrawal
    {
        $admin = $this->authorizedAdmin('sales.eu_withdrawals.mark_refunded', 'bagistoapi::app.admin.eu-withdrawal.no-permission');

        $withdrawal = $this->findOrFail($id);

        $validator = Validator::make(['refund_note' => $note], [
            'refund_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $withdrawal->update([
            'status'              => WithdrawalStatus::REFUNDED,
            'refunded_at'         => now(),
            'refund_note'         => $note,
            'refunded_by_user_id' => $admin->id,
            'declined_at'         => null,
            'declined_reason'     => null,
            'declined_by_user_id' => null,
        ]);

        return $this->result($id, __('bagistoapi::app.admin.eu-withdrawal.refunded'));
    }

    private function handleResend(int $id): AdminEuWithdrawal
    {
        $this->authorizedAdmin('sales.eu_withdrawals.resend_confirmation', 'bagistoapi::app.admin.eu-withdrawal.no-permission');

        $withdrawal = $this->findOrFail($id);

        $previousLocale = app()->getLocale();
        app()->setLocale($withdrawal->locale);

        $isTerminal = WithdrawalStatus::isTerminal($withdrawal->status);

        try {
            Mail::send(new WithdrawalConfirmation($withdrawal));

            $updates = ['confirmation_error' => null];

            if ($isTerminal) {
                $updates['final_confirmation_sent_at'] = now();
            } elseif ($withdrawal->confirmation_sent_at === null) {
                $updates['confirmation_sent_at'] = now();
            }

            $withdrawal->update($updates);

            $message = __('bagistoapi::app.admin.eu-withdrawal.confirmation-resent');
        } catch (\Throwable $e) {
            $withdrawal->update(['confirmation_error' => mb_substr($e->getMessage(), 0, 500)]);

            app()->setLocale($previousLocale);

            throw new InvalidInputException(__('bagistoapi::app.admin.eu-withdrawal.confirmation-failed'), 422);
        } finally {
            app()->setLocale($previousLocale);
        }

        return $this->result($id, $message);
    }

    private function findOrFail(int $id): object
    {
        $withdrawal = $this->withdrawals->find($id);

        if (! $withdrawal) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.eu-withdrawal.not-found'));
        }

        return $withdrawal;
    }

    private function result(int $id, string $message): AdminEuWithdrawal
    {
        $dto = $this->mapWithdrawalRow($this->baseWithdrawalQuery()->where('w.id', $id)->first());
        $dto->message = $message;

        return $dto;
    }

    private function resolveId(mixed $data, array $uriVariables): int
    {
        if (isset($uriVariables['id'])) {
            return (int) $uriVariables['id'];
        }

        $iri = is_object($data) ? ($data->id ?? null) : null;

        return $iri ? (int) basename((string) $iri) : 0;
    }
}
