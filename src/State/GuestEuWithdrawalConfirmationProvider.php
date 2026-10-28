<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\GuestEuWithdrawalConfirmation;
use Webkul\EUWithdrawal\Repositories\WithdrawalRepository;

class GuestEuWithdrawalConfirmationProvider implements ProviderInterface
{
    /**
     * Lookups allowed per minute for one IP and withdrawal.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Create a new provider instance.
     */
    public function __construct(protected WithdrawalRepository $withdrawals) {}

    /**
     * Find a guest withdrawal only after proving ownership with its order email.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): GuestEuWithdrawalConfirmation
    {
        $uuid = $uriVariables['uuid'] ?? $context['args']['uuid'] ?? request()->query('uuid');
        $email = request()->query('email') ?? ($context['args']['email'] ?? null);

        return $this->provideForProof($uuid, $email);
    }

    /**
     * Return a guest withdrawal only when the email matches its order, answering alike for every failure.
     */
    public function provideForProof(?string $uuid, ?string $email): GuestEuWithdrawalConfirmation
    {
        $validator = Validator::make([
            'uuid' => $uuid,
            'email' => $email,
        ], [
            'uuid' => ['required', 'uuid'],
            'email' => ['required', 'email', 'max:191'],
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $key = 'eu-withdrawal-confirmation:'.request()->ip().':'.$uuid;

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.not-found'));
        }

        RateLimiter::hit($key, 60);

        $withdrawal = $this->withdrawals->getModel()
            ->newQuery()
            ->with('order')
            ->where('uuid', $uuid)
            ->where('is_guest', true)
            ->first();

        if (
            ! $withdrawal
            || ! $withdrawal->order
            || ! hash_equals(mb_strtolower((string) $withdrawal->order->customer_email), mb_strtolower((string) $email))
            || ! core()->getConfigData('sales.eu_withdrawal.general.enabled', optional($withdrawal->order->channel)->code)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.not-found'));
        }

        $confirmation = new GuestEuWithdrawalConfirmation;
        $confirmation->uuid = $withdrawal->uuid;
        $confirmation->orderIncrementId = $withdrawal->order->increment_id;
        $confirmation->status = $withdrawal->status;
        $confirmation->receivedAt = $withdrawal->received_at?->toIso8601String();

        return $confirmation;
    }
}
