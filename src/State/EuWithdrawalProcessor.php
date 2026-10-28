<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\CreateEuWithdrawalInput;
use Webkul\BagistoApi\Dto\CreateGuestEuWithdrawalInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\EuWithdrawal;
use Webkul\BagistoApi\Models\GuestEuWithdrawal;
use Webkul\BagistoApi\State\Concerns\BuildsEuWithdrawal;
use Webkul\BagistoApi\State\Concerns\ThrottlesGuestRequests;
use Webkul\EUWithdrawal\Services\WithdrawalService;
use Webkul\Sales\Repositories\OrderRepository;

class EuWithdrawalProcessor implements ProcessorInterface
{
    use BuildsEuWithdrawal;
    use ThrottlesGuestRequests;

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
            return $this->handleGuest($data->link, $data->reason_text);
        }

        if ($data instanceof GuestEuWithdrawal) {
            return $this->handleGuest(request()->input('link'), request()->input('reason_text'));
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

    private function handleGuest(?string $link, ?string $reasonText): GuestEuWithdrawal
    {
        $this->throttleGuestRequest('eu-withdraw-submit', 20);

        $validator = Validator::make(['link' => $link, 'reason_text' => $reasonText], [
            'link' => ['required', 'string', 'max:2048'],
            'reason_text' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $order = $this->orders->find($this->orderIdFromSignedLink($link));

        if (! $order || ! $order->is_guest || ! $this->channelEnabled($order)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.order-not-found'));
        }

        $withdrawal = $this->service->submit($order, $reasonText, app()->getLocale());

        return $this->buildGuestEuWithdrawal($withdrawal->loadMissing('order'));
    }

    /**
     * Reads the order id from the emailed guest-create link, which must carry a valid, unexpired signature.
     */
    private function orderIdFromSignedLink(string $link): int
    {
        $path = (string) parse_url($link, PHP_URL_PATH);

        if (
            ! preg_match('#/withdraw/(\d+)/create$#', $path, $matches)
            || ! URL::hasValidSignature(Request::create($link))
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.eu-withdrawal.invalid-link'), 422);
        }

        return (int) $matches[1];
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
}
