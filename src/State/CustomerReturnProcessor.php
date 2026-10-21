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
use Webkul\BagistoApi\State\Concerns\ReadsReturnImages;
use Webkul\BagistoApi\State\Concerns\ResolvesReturnableItems;
use Webkul\BagistoApi\State\Concerns\ResolvesReturnCustomFields;
use Webkul\RMA\Enums\DefaultRMAResolution;
use Webkul\RMA\Enums\DefaultRMAStatusEnum;
use Webkul\RMA\Helpers\Helper as RMAHelper;
use Webkul\RMA\Repositories\RMAAdditionalFieldRepository;
use Webkul\RMA\Repositories\RMACustomFieldRepository;
use Webkul\RMA\Repositories\RMAImageRepository;
use Webkul\RMA\Repositories\RMAItemRepository;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Shop\Mail\Customer\RMA\CustomerRMARequestNotification;

class CustomerReturnProcessor implements ProcessorInterface
{
    use BuildsCustomerReturn;
    use ReadsReturnImages;
    use ResolvesReturnableItems;
    use ResolvesReturnCustomFields;

    /**
     * Package conditions the storefront form offers.
     */
    public const PACKAGE_CONDITIONS = ['open', 'packed'];

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMARepository $rmaRepository,
        private readonly RMAItemRepository $rmaItemRepository,
        private readonly RMAImageRepository $rmaImageRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
        private readonly RMAHelper $rmaHelper,
        private readonly OrderRepository $orderRepository,
        private readonly RMACustomFieldRepository $rmaCustomFieldRepository,
        private readonly RMAAdditionalFieldRepository $rmaAdditionalFieldRepository,
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CreateCustomerReturnInput) {
            return $this->handleCreate($data, []);
        }

        if ($data instanceof CustomerReturnActionInput) {
            return $this->handleAction($operation->getName(), self::basename($data->id));
        }

        if ($operation instanceof Post) {
            $action = $this->actionFromTemplate($operation->getUriTemplate());

            if ($action !== null) {
                return $this->handleAction($action, $uriVariables['id'] ?? null);
            }

            return $this->handleCreate($this->inputFromRequest(), $this->returnImagesFromRequest());
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Build the create input from a REST request body (JSON or multipart).
     */
    private function inputFromRequest(): CreateCustomerReturnInput
    {
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

        $customAttributes = request()->input('custom_attributes', request()->input('customAttributes'));

        if (is_string($customAttributes)) {
            $customAttributes = json_decode($customAttributes, true);
        }

        $input->custom_attributes = is_array($customAttributes) ? $customAttributes : null;

        return $input;
    }

    /**
     * Handle create.
     */
    private function handleCreate(CreateCustomerReturnInput $input, array $images): CustomerReturn
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if ($input->agreement !== true) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.agreement-required'));
        }

        $validResolutions = array_map(fn ($resolution) => $resolution->value, DefaultRMAResolution::cases());

        if (
            empty($input->order_id)
            || empty($input->order_item_id)
            || empty($input->rma_reason_id)
            || (int) $input->rma_qty < 1
            || ! in_array($input->resolution_type, $validResolutions, true)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-input'));
        }

        if (
            ! empty($input->package_condition)
            && ! in_array($input->package_condition, self::PACKAGE_CONDITIONS, true)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-package-condition'));
        }

        $customAttributes = $this->validateReturnCustomAttributes(
            $input->custom_attributes ?? [],
            $this->rmaCustomFieldRepository
        );

        $order = $this->orderRepository->findOneWhere([
            'id' => $input->order_id,
            'customer_id' => $customer->id,
        ]);

        if (! $order) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-order'));
        }

        $eligibleItem = $this->resolveReturnableItems($this->rmaHelper, $this->rmaItemRepository, (int) $order->id)
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
            'customAttributes' => $customAttributes,
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

        if (! empty($customAttributes)) {
            $this->rmaAdditionalFieldRepository->createManyForRma($rma->id, $customAttributes);
        }

        Event::dispatch('customer.rma.request.create.after', $rma);

        try {
            Mail::queue(new CustomerRMARequestNotification($rma));
        } catch (\Exception) {
        }

        $fresh = $this->rmaRepository->with(self::RETURN_RELATIONS)->find($rma->id);

        return $this->buildCustomerReturn($fresh, true, $this->rmaRepository);
    }

    /**
     * Handle action.
     */
    private function handleAction(?string $action, mixed $id): CustomerReturn
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $rma = $this->rmaRepository->with(self::RETURN_RELATIONS)
            ->whereHas('order', fn ($query) => $query->where('customer_id', $customer->id))
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

    /**
     * Cancel the return request when allowed.
     */
    private function doCancel($rma): void
    {
        if ((int) $rma->rma_status_id === DefaultRMAStatusEnum::CANCELED->value) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.already-canceled'));
        }

        if (! $this->rmaRepository->canCancelRma($rma)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.cancel-not-allowed'));
        }

        $rma->update(['rma_status_id' => DefaultRMAStatusEnum::CANCELED->value]);
    }

    /**
     * Reopen the return request when allowed.
     */
    private function doReopen($rma): void
    {
        if (
            $this->rmaRepository->isRmaExpired($rma)
            || ! $this->rmaRepository->canReopenRma($rma)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.reopen-not-allowed'));
        }

        $rma->update(['rma_status_id' => DefaultRMAStatusEnum::PENDING->value]);

        $this->rmaMessageRepository->create([
            'rma_id' => $rma->id,
            'message' => trans('shop::app.rma.mail.customer-conversation.process'),
            'is_admin' => 1,
        ]);
    }

    /**
     * Close the return request when allowed.
     */
    private function doClose($rma): void
    {
        if (
            $this->rmaRepository->isRmaExpired($rma)
            || ! $this->rmaRepository->canCloseRma($rma)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.close-not-allowed'));
        }

        $rma->update(['rma_status_id' => DefaultRMAStatusEnum::SOLVED->value]);

        $this->rmaMessageRepository->create([
            'rma_id' => $rma->id,
            'message' => trans('shop::app.rma.mail.customer-conversation.solved'),
            'is_admin' => 1,
        ]);
    }

    /**
     * Action from template.
     */
    private function actionFromTemplate(?string $template): ?string
    {
        foreach (['cancel', 'reopen', 'close'] as $action) {
            if (
                $template !== null
                && str_contains($template, '/'.$action)
            ) {
                return $action;
            }
        }

        return null;
    }

    /**
     * Basename.
     */
    private static function basename(?string $value): ?string
    {
        return $value === null ? null : basename($value);
    }

    /**
     * Int or null.
     */
    private static function intOrNull($value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
