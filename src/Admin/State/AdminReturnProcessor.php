<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminCreateReturnInput;
use Webkul\BagistoApi\Admin\Dto\AdminReturnActionInput;
use Webkul\BagistoApi\Admin\Dto\AdminReturnUpdateStatusInput;
use Webkul\BagistoApi\Admin\Models\AdminReturn;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminReturn;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Enums\DefaultRMAResolution;
use Webkul\RMA\Enums\DefaultRMAStatusEnum;
use Webkul\RMA\Repositories\RMAAdditionalFieldRepository;
use Webkul\RMA\Repositories\RMAImageRepository;
use Webkul\RMA\Repositories\RMAItemRepository;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\RMA\Repositories\RMAStatusRepository;
use Webkul\Sales\Exceptions\InvalidRefundQuantityException;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\RefundRepository;
use Webkul\Shop\Mail\Customer\RMA\CustomerRMARequestNotification;
use Webkul\Shop\Mail\Customer\RMA\CustomerRMAStatusNotification;

class AdminReturnProcessor implements ProcessorInterface
{
    use BuildsAdminReturn;
    use ChecksAdminPermission;

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly RMARepository $rmaRepository,
        private readonly RMAItemRepository $rmaItemRepository,
        private readonly RMAImageRepository $rmaImageRepository,
        private readonly RMAMessageRepository $rmaMessageRepository,
        private readonly RMAAdditionalFieldRepository $rmaAdditionalFieldRepository,
        private readonly RMAStatusRepository $rmaStatusRepository,
        private readonly OrderItemRepository $orderItemRepository,
        private readonly OrderRepository $orderRepository,
        private readonly RefundRepository $refundRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof AdminCreateReturnInput) {
            return $this->handleCreate($this->inputToArray($data), []);
        }

        if ($data instanceof AdminReturnUpdateStatusInput) {
            return $this->handleUpdateStatus(self::basename($data->id), $data->rma_status_id, $data->shipping);
        }

        if ($data instanceof AdminReturnActionInput) {
            return $this->handleReopen(self::basename($data->id));
        }

        if ($data instanceof AdminReturn && $operation instanceof Post) {
            $action = $this->actionFromTemplate($operation->getUriTemplate());

            if ($action === 'update-status') {
                return $this->handleUpdateStatus(
                    $uriVariables['id'] ?? null,
                    self::intOrNull(request()->input('rma_status_id')),
                    request()->input('shipping'),
                );
            }

            if ($action === 'reopen') {
                return $this->handleReopen($uriVariables['id'] ?? null);
            }

            $input = [
                'order_id' => self::intOrNull(request()->input('order_id')),
                'order_item_id' => self::intOrNull(request()->input('order_item_id')),
                'rma_qty' => self::intOrNull(request()->input('rma_qty')),
                'resolution_type' => request()->input('resolution_type'),
                'rma_reason_id' => self::intOrNull(request()->input('rma_reason_id')),
                'information' => request()->input('information'),
                'package_condition' => request()->input('package_condition'),
                'variant' => self::intOrNull(request()->input('variant')),
            ];

            $images = request()->hasFile('images') ? request()->file('images') : [];

            return $this->handleCreate($input, $images);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleCreate(array $input, array $images): AdminReturn
    {
        $this->authorizedAdmin('sales.rma.requests.create', 'bagistoapi::app.admin.rma.no-permission');

        $validator = Validator::make($input, [
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'required',
            'rma_qty' => 'required|integer|min:1',
            'resolution_type' => 'required',
            'rma_reason_id' => 'required',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $orderItem = $this->orderItemRepository->find($input['order_item_id']);

        if ($orderItem) {
            $maxQty = $input['resolution_type'] === DefaultRMAResolution::CANCEL_ITEMS->value
                ? (int) $orderItem->qty_ordered - (int) $orderItem->qty_invoiced - (int) $orderItem->qty_canceled
                : (int) $orderItem->qty_invoiced - (int) $orderItem->qty_refunded;

            if ((int) $input['rma_qty'] > max($maxQty, 0)) {
                throw new InvalidInputException(__('bagistoapi::app.admin.rma.qty-exceeds'), 422);
            }
        }

        Event::dispatch('sales.rma.request.create.before', $input);

        $rma = $this->rmaRepository->create([
            'order_id' => $input['order_id'],
            'rma_status_id' => DefaultRMAStatusEnum::PENDING->value,
            'information' => $input['information'] ?? null,
            'package_condition' => $input['package_condition'] ?? null,
        ]);

        $this->rmaItemRepository->create([
            'rma_id' => $rma->id,
            'rma_reason_id' => $input['rma_reason_id'],
            'order_item_id' => $input['order_item_id'],
            'variant_id' => ! empty($input['variant']) ? $input['variant'] : null,
            'quantity' => $input['rma_qty'],
            'resolution' => $input['resolution_type'],
        ]);

        $this->rmaMessageRepository->create([
            'rma_id' => $rma->id,
            'message' => trans('shop::app.rma.mail.customer-conversation.process'),
            'is_admin' => 1,
        ]);

        if (! empty($images)) {
            $this->rmaImageRepository->manageImages($images, $rma);
        }

        $customAttributes = request('customAttributes', []);

        if (! empty($customAttributes)) {
            $this->rmaAdditionalFieldRepository->createManyForRma($rma->id, $customAttributes);
        }

        Event::dispatch('sales.rma.request.create.after', $rma);

        try {
            Mail::queue(new CustomerRMARequestNotification($rma));
        } catch (\Exception) {
        }

        $fresh = $this->rmaRepository->with(self::RETURN_RELATIONS)->find($rma->id);

        return $this->buildAdminReturn($fresh, $this->rmaRepository, $this->rmaStatusRepository);
    }

    private function handleUpdateStatus(mixed $id, ?int $statusId, $shipping): AdminReturn
    {
        $this->authorizedAdmin('sales.rma.requests', 'bagistoapi::app.admin.rma.no-permission');

        $rma = $this->findOwned($id);

        if ($statusId === null || ! $this->rmaStatusRepository->find($statusId)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.invalid-status'), 422);
        }

        $totalCount = $this->rmaItemRepository->where('rma_id', $rma->id)->sum('quantity');

        if (empty($totalCount)) {
            return $this->finalizeRmaUpdate($rma, $statusId);
        }

        return match ($statusId) {
            DefaultRMAStatusEnum::RECEIVED_PACKAGE->value => $this->handleReceivedPackage($rma, $statusId, $shipping),
            DefaultRMAStatusEnum::ITEM_CANCELED->value => $this->handleItemCancellation($rma, $statusId),
            default => $this->finalizeRmaUpdate($rma, $statusId),
        };
    }

    private function handleReceivedPackage($rma, int $statusId, $shipping): AdminReturn
    {
        try {
            DB::beginTransaction();

            $refundData = [
                'refund' => [
                    'shipping' => $shipping ?? 0,
                    'adjustment_refund' => 0,
                    'adjustment_fee' => 0,
                    'items' => [$rma->item->order_item_id => $rma->item->quantity],
                ],
            ];

            $this->processOrderRefund($rma->order, $refundData);

            $result = $this->finalizeRmaUpdate($rma, $statusId);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new InvalidInputException($e->getMessage() ?: __('bagistoapi::app.admin.rma.refund-failed'), 422);
        }
    }

    private function handleItemCancellation($rma, int $statusId): AdminReturn
    {
        try {
            DB::beginTransaction();

            $this->cancelRmaItem($rma);

            $result = $this->finalizeRmaUpdate($rma, $statusId);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new InvalidInputException($e->getMessage(), 422);
        }
    }

    private function processOrderRefund($order, array $refundData): void
    {
        if (! $order->canRefund()) {
            throw new \Exception(__('bagistoapi::app.admin.rma.refund-failed'));
        }

        $totals = $this->refundRepository->getOrderItemsRefundSummary($refundData['refund'], $order->id);

        if (! $totals) {
            throw new InvalidRefundQuantityException(__('bagistoapi::app.admin.rma.refund-failed'));
        }

        $maxRefundAmount = $totals['grand_total']['price'] - $order->refunds()->sum('base_adjustment_refund');

        $refundAmount = $totals['grand_total']['price'] - $totals['shipping']['price']
            + $refundData['refund']['shipping']
            + $refundData['refund']['adjustment_refund']
            - $refundData['refund']['adjustment_fee'];

        if (! $refundAmount || $refundAmount > $maxRefundAmount) {
            throw new \Exception(__('bagistoapi::app.admin.rma.refund-failed'));
        }

        $this->refundRepository->create(array_merge($refundData, ['order_id' => $order->id]));
    }

    private function cancelRmaItem($rma): void
    {
        if ($rma->item && ($orderItem = $rma->item->orderItem)) {
            $this->orderItemRepository->returnQtyToProductInventory($orderItem);

            $cancelableQty = max(0, (int) $orderItem->qty_ordered - (int) $orderItem->qty_invoiced - (int) $orderItem->qty_canceled);
            $quantity = min((int) $rma->item->quantity, $cancelableQty);

            if ($orderItem->qty_ordered) {
                $orderItem->qty_canceled += $quantity;
                $orderItem->save();

                if ($orderItem->parent && $orderItem->parent->qty_ordered) {
                    $orderItem->parent->qty_canceled += $orderItem->parent->qty_to_cancel;
                    $orderItem->parent->save();
                }
            } elseif ($orderItem->parent) {
                $orderItem->parent->qty_canceled += $orderItem->parent->qty_to_cancel;
                $orderItem->parent->save();
            }
        }

        $this->orderRepository->updateOrderStatus($rma->order);

        Event::dispatch('sales.order.cancel.after', $rma->order);
    }

    private function finalizeRmaUpdate($rma, int $statusId): AdminReturn
    {
        $rma->update(['rma_status_id' => $statusId]);

        $this->rmaMessageRepository->create([
            'message' => trans('admin::app.sales.rma.all-rma.view.status-message', [
                'id' => $rma->id,
                'status' => $rma->fresh()->status->title,
            ]),
            'rma_id' => $rma->id,
            'is_admin' => 1,
        ]);

        try {
            Mail::queue(new CustomerRMAStatusNotification($rma));
        } catch (\Exception) {
        }

        $fresh = $this->rmaRepository->with(self::RETURN_RELATIONS)->find($rma->id);

        return $this->buildAdminReturn($fresh, $this->rmaRepository, $this->rmaStatusRepository);
    }

    private function handleReopen(mixed $id): AdminReturn
    {
        $this->authorizedAdmin('sales.rma.requests', 'bagistoapi::app.admin.rma.no-permission');

        $rma = $this->findOwned($id);

        if (! $this->rmaRepository->canReopenRma($rma)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.rma.reopen-not-allowed'), 422);
        }

        $rma->update(['rma_status_id' => DefaultRMAStatusEnum::PENDING->value]);

        $this->rmaMessageRepository->create([
            'message' => trans('admin::app.sales.rma.all-rma.view.conversation-process'),
            'rma_id' => $rma->id,
            'is_admin' => 1,
        ]);

        try {
            Mail::queue(new CustomerRMAStatusNotification($rma));
        } catch (\Exception) {
        }

        $fresh = $this->rmaRepository->with(self::RETURN_RELATIONS)->find($rma->id);

        return $this->buildAdminReturn($fresh, $this->rmaRepository, $this->rmaStatusRepository);
    }

    private function findOwned(mixed $id): object
    {
        $rma = $this->rmaRepository->with(self::RETURN_RELATIONS)->find($id);

        if (! $rma) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.not-found'));
        }

        return $rma;
    }

    private function actionFromTemplate(?string $template): ?string
    {
        foreach (['update-status', 'reopen'] as $action) {
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

    private function inputToArray(AdminCreateReturnInput $dto): array
    {
        return [
            'order_id' => $dto->order_id,
            'order_item_id' => $dto->order_item_id,
            'rma_qty' => $dto->rma_qty,
            'resolution_type' => $dto->resolution_type,
            'rma_reason_id' => $dto->rma_reason_id,
            'information' => $dto->information,
            'package_condition' => $dto->package_condition,
            'variant' => $dto->variant,
        ];
    }

    private static function intOrNull($value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
