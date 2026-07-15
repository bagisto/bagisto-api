<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\CreateEuWithdrawalInput;
use Webkul\BagistoApi\Dto\CreateGuestEuWithdrawalInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\EuWithdrawal;
use Webkul\BagistoApi\Models\GuestEuWithdrawal;
use Webkul\BagistoApi\State\Concerns\BuildsEuWithdrawal;
use Webkul\EUWithdrawal\Services\WithdrawalService;
use Webkul\Sales\Repositories\OrderRepository;

class EuWithdrawalProcessor implements ProcessorInterface
{
    use BuildsEuWithdrawal;

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly OrderRepository $orders,
        private readonly WithdrawalService $service,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CreateEuWithdrawalInput) {
            return $this->handleCustomer((int) $data->order_id, $data->reason_text);
        }

        if ($data instanceof CreateGuestEuWithdrawalInput) {
            return $this->handleGuest($data->order_increment_id, $data->email, $data->reason_text);
        }

        if ($data instanceof GuestEuWithdrawal) {
            return $this->handleGuest(
                request()->input('order_increment_id'),
                request()->input('email'),
                request()->input('reason_text'),
            );
        }

        if ($data instanceof EuWithdrawal) {
            return $this->handleCustomer(
                (int) request()->input('order_id'),
                request()->input('reason_text'),
            );
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleCustomer(int $orderId, ?string $reasonText): EuWithdrawal
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $this->validateReason($reasonText);

        $order = $this->orders->getModel()
            ->newQuery()
            ->where('id', $orderId)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $order) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.order-not-found'));
        }

        $this->assertChannelEnabled($order);

        $withdrawal = $this->service->submit($order, $reasonText, app()->getLocale());

        return $this->buildEuWithdrawal($withdrawal->loadMissing('order'));
    }

    private function handleGuest(?string $incrementId, ?string $email, ?string $reasonText): GuestEuWithdrawal
    {
        $this->validateGuest($incrementId, $email, $reasonText);

        $currentChannel = core()->getCurrentChannel();

        $order = $this->orders->getModel()
            ->newQuery()
            ->where('increment_id', $incrementId)
            ->where('customer_email', $email)
            ->where('is_guest', 1)
            ->when($currentChannel, fn ($q) => $q->where('channel_id', $currentChannel->id))
            ->first();

        if (! $order || ! $this->channelEnabled($order)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.order-not-found'));
        }

        $withdrawal = $this->service->submit($order, $reasonText, app()->getLocale());

        return $this->buildGuestEuWithdrawal($withdrawal->loadMissing('order'));
    }

    private function assertChannelEnabled(object $order): void
    {
        if (! $this->channelEnabled($order)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.order-not-found'));
        }
    }

    private function channelEnabled(object $order): bool
    {
        return (bool) core()->getConfigData('sales.eu_withdrawal.general.enabled', optional($order->channel)->code);
    }

    private function validateReason(?string $reasonText): void
    {
        $validator = Validator::make(['reason_text' => $reasonText], [
            'reason_text' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }

    private function validateGuest(?string $incrementId, ?string $email, ?string $reasonText): void
    {
        $validator = Validator::make([
            'order_increment_id' => $incrementId,
            'email' => $email,
            'reason_text' => $reasonText,
        ], [
            'order_increment_id' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:191'],
            'reason_text' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }
}
