<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Dto\CreateCustomerReturnInput;
use Webkul\BagistoApi\Dto\CustomerReturnActionInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerReturn;
use Webkul\BagistoApi\State\Concerns\BuildsCustomerReturn;
use Webkul\RMA\Enums\DefaultRMAResolution;
use Webkul\RMA\Enums\DefaultRMAStatusEnum;
use Webkul\RMA\Helpers\Helper as RMAHelper;
use Webkul\RMA\Repositories\RMAImageRepository;
use Webkul\RMA\Repositories\RMAItemRepository;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Shop\Mail\Customer\RMA\CustomerRMARequestNotification;

class CustomerReturnProcessor implements ProcessorInterface
{
    use BuildsCustomerReturn;

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMARepository $rmaRepository,
        private readonly RMAItemRepository $rmaItemRepository,
        private readonly RMAImageRepository $rmaImageRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
        private readonly RMAHelper $rmaHelper,
        private readonly OrderRepository $orderRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CreateCustomerReturnInput) {
            return $this->handleCreate($data, []);
        }

        if ($data instanceof CustomerReturnActionInput) {
            return $this->handleAction($operation->getName(), self::basename($data->id));
        }

        if ($data instanceof CustomerReturn && $operation instanceof Post) {
            $action = $this->actionFromTemplate($operation->getUriTemplate());

            if ($action !== null) {
                return $this->handleAction($action, $uriVariables['id'] ?? null);
            }

            $input = new CreateCustomerReturnInput;
            $input->order_id = self::intOrNull(request()->input('order_id'));
            $input->order_item_id = self::intOrNull(request()->input('order_item_id'));
            $input->rma_qty = self::intOrNull(request()->input('rma_qty'));
            $input->resolution_type = request()->input('resolution_type');
            $input->rma_reason_id = self::intOrNull(request()->input('rma_reason_id'));
            $input->information = request()->input('information');
            $input->package_condition = request()->input('package_condition');
            $input->variant = self::intOrNull(request()->input('variant'));
            $input->agreement = filter_var(request()->input('agreement'), FILTER_VALIDATE_BOOLEAN);

            $images = request()->hasFile('images') ? request()->file('images') : [];

            return $this->handleCreate($input, $images);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleCreate(CreateCustomerReturnInput $input, array $images): CustomerReturn
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if ($input->agreement !== true) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.agreement-required'));
        }

        $validResolutions = array_map(fn ($c) => $c->value, DefaultRMAResolution::cases());

        if (
            empty($input->order_id)
            || empty($input->order_item_id)
            || empty($input->rma_reason_id)
            || (int) $input->rma_qty < 1
            || ! in_array($input->resolution_type, $validResolutions, true)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-input'));
        }

        $order = $this->orderRepository->findOneWhere([
            'id' => $input->order_id,
            'customer_id' => $customer->id,
        ]);

        if (! $order) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-order'));
        }

        $eligibleItem = $this->rmaHelper->getOrderItems($order->id)
            ->firstWhere('order_item_id', (int) $input->order_item_id);

        if (! $eligibleItem) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-item'));
        }

        $resolutionMax = $input->resolution_type === DefaultRMAResolution::CANCEL_ITEMS->value
            ? (int) $eligibleItem->forCancelQuantity
            : (int) $eligibleItem->forReturnQuantity;

        $maxQty = max(0, min($resolutionMax, (int) $eligibleItem->currentQuantity));

        if ((int) $input->rma_qty > $maxQty) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.qty-exceeds'));
        }

        $payload = [
            'order_id' => $order->id,
            'order_item_id' => $input->order_item_id,
            'rma_qty' => $input->rma_qty,
            'resolution_type' => $input->resolution_type,
            'rma_reason_id' => $input->rma_reason_id,
            'information' => $input->information,
            'package_condition' => $input->package_condition,
            'variant' => $input->variant,
        ];

        Event::dispatch('customer.rma.request.create.before', $payload);

        $rma = $this->rmaRepository->create([
            'order_id' => $order->id,
            'rma_status_id' => DefaultRMAStatusEnum::PENDING->value,
            'information' => $input->information,
            'package_condition' => $input->package_condition,
        ]);

        $this->rmaItemRepository->create([
            'rma_id' => $rma->id,
            'rma_reason_id' => $input->rma_reason_id,
            'order_item_id' => $input->order_item_id,
            'variant_id' => ! empty($input->variant) ? $input->variant : null,
            'quantity' => $input->rma_qty,
            'resolution' => $input->resolution_type,
        ]);

        $this->rmaMessageRepository->create([
            'rma_id' => $rma->id,
            'message' => trans('shop::app.rma.mail.customer-conversation.process'),
            'is_admin' => 1,
        ]);

        if (! empty($images)) {
            $this->rmaImageRepository->manageImages($images, $rma);
        }

        Event::dispatch('customer.rma.request.create.after', $rma);

        try {
            Mail::queue(new CustomerRMARequestNotification($rma));
        } catch (\Exception) {
        }

        $fresh = $this->rmaRepository->with(self::RETURN_RELATIONS)->find($rma->id);

        return $this->buildCustomerReturn($fresh, true, $this->rmaRepository);
    }

    private function handleAction(?string $action, mixed $id): CustomerReturn
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $rma = $this->rmaRepository->with(self::RETURN_RELATIONS)
            ->whereHas('order', fn ($q) => $q->where('customer_id', $customer->id))
            ->find($id);

        if (! $rma) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.not-found'));
        }

        Event::dispatch('customer.rma.request.update.before', $rma->id);

        match ($action) {
            'cancel' => $this->doCancel($rma),
            'reopen' => $this->doReopen($rma),
            'close' => $this->doClose($rma),
            default => throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-input')),
        };

        Event::dispatch('customer.rma.request.update.after', $rma);

        $fresh = $this->rmaRepository->with(self::RETURN_RELATIONS)->find($rma->id);

        return $this->buildCustomerReturn($fresh, true, $this->rmaRepository);
    }

    private function doCancel($rma): void
    {
        if ((int) $rma->rma_status_id === DefaultRMAStatusEnum::CANCELED->value) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.already-canceled'));
        }

        $rma->update(['rma_status_id' => DefaultRMAStatusEnum::CANCELED->value]);
    }

    private function doReopen($rma): void
    {
        if (! $this->rmaRepository->canReopenRma($rma)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.reopen-not-allowed'));
        }

        $rma->update(['rma_status_id' => DefaultRMAStatusEnum::PENDING->value]);

        $this->rmaMessageRepository->create([
            'rma_id' => $rma->id,
            'message' => trans('shop::app.rma.mail.customer-conversation.process'),
            'is_admin' => 1,
        ]);
    }

    private function doClose($rma): void
    {
        $rma->update(['rma_status_id' => DefaultRMAStatusEnum::SOLVED->value]);

        $this->rmaMessageRepository->create([
            'rma_id' => $rma->id,
            'message' => trans('shop::app.rma.mail.customer-conversation.solved'),
            'is_admin' => 1,
        ]);
    }

    private function actionFromTemplate(?string $template): ?string
    {
        foreach (['cancel', 'reopen', 'close'] as $action) {
            if ($template !== null && str_contains($template, '/'.$action)) {
                return $action;
            }
        }

        return null;
    }

    private static function basename(?string $value): ?string
    {
        return $value === null ? null : basename($value);
    }

    private static function intOrNull($value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
