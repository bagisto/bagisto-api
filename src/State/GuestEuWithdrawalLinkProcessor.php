<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\CreateGuestEuWithdrawalLinkInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\GuestEuWithdrawalLink;
use Webkul\BagistoApi\State\Concerns\ThrottlesGuestRequests;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Shop\Mail\Customer\EUWithdrawal\GuestWithdrawalLink;

/**
 * Mirrors the storefront EUWithdrawalController::lookupSubmit.
 */
class GuestEuWithdrawalLinkProcessor implements ProcessorInterface
{
    use ThrottlesGuestRequests;

    public function __construct(protected OrderRepository $orders) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): GuestEuWithdrawalLink
    {
        if (! core()->getConfigData('sales.eu_withdrawal.general.enabled', core()->getCurrentChannelCode())) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.eu-withdrawal.disabled'));
        }

        $this->throttleGuestRequest('eu-withdraw-lookup', 5);

        $input = [
            'order_increment_id' => $this->value($data, $context, 'order_increment_id', 'orderIncrementId'),
            'email' => $this->value($data, $context, 'email', 'email'),
        ];

        $validator = Validator::make($input, [
            'order_increment_id' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:191'],
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $currentChannel = core()->getCurrentChannel();

        $order = $this->orders->getModel()
            ->newQuery()
            ->where('increment_id', $input['order_increment_id'])
            ->where('customer_email', $input['email'])
            ->where('is_guest', 1)
            ->when($currentChannel, fn ($query) => $query->where('channel_id', $currentChannel->id))
            ->first();

        if ($order && core()->getConfigData('sales.eu_withdrawal.general.enabled', optional($order->channel)->code)) {
            try {
                Mail::queue(new GuestWithdrawalLink(
                    toEmail: $order->customer_email,
                    signedUrl: URL::temporarySignedRoute('shop.eu-withdrawal.guest.create', now()->addHours(24), ['orderId' => $order->id]),
                    orderIncrementId: $order->increment_id,
                ));
            } catch (\Throwable $e) {
                Log::error('EU withdrawal guest-link email failed', ['error' => $e->getMessage()]);
            }
        }

        $result = new GuestEuWithdrawalLink;
        $result->success = true;
        $result->message = __('bagistoapi::app.graphql.eu-withdrawal.link-sent');

        return $result;
    }

    protected function value(mixed $data, array $context, string $snake, string $camel): ?string
    {
        $value = $data instanceof CreateGuestEuWithdrawalLinkInput ? $data->{$snake} : null;
        $value ??= $context['args']['input'][$camel] ?? $context['args']['input'][$snake] ?? request()->input($snake) ?? request()->input($camel);

        return is_scalar($value) ? (string) $value : null;
    }
}
