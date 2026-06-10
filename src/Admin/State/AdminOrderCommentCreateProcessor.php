<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Models\AdminOrderComment;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Repositories\OrderCommentRepository;

/**
 * POST /api/admin/orders/{orderId}/comments + createAdminOrderComment mutation.
 *
 * Mirrors `Admin\Sales\OrderController::comment` — fires the before/after
 * events so Bagisto core listeners can send the customer email when
 * `customer_notified = 1`.
 */
class AdminOrderCommentCreateProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected OrderCommentRepository $commentRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminOrderComment
    {
        $this->guard->resolveAdmin();
        $order = $this->guard->resolveOrder($uriVariables, $context, 'orderId');

        $comment = $this->extractComment($data, $context);

        if ($comment === null || trim($comment) === '') {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.comment.empty'), 422);
        }

        $customerNotified = (bool) $this->extractCustomerNotified($data, $context);

        Event::dispatch('sales.order.comment.create.before');

        $row = $this->commentRepository->create([
            'order_id'          => $order->id,
            'comment'           => $comment,
            'customer_notified' => $customerNotified ? 1 : 0,
        ]);

        Event::dispatch('sales.order.comment.create.after', $row);

        return $this->toDto($row);
    }

    protected function extractComment(mixed $data, array $context): ?string
    {
        if (is_object($data) && property_exists($data, 'comment') && $data->comment !== null) {
            return (string) $data->comment;
        }

        return $context['args']['input']['comment']
            ?? request()->input('comment')
            ?? null;
    }

    protected function extractCustomerNotified(mixed $data, array $context): bool
    {
        if (is_object($data) && property_exists($data, 'customerNotified') && $data->customerNotified !== null) {
            return (bool) $data->customerNotified;
        }

        return (bool) ($context['args']['input']['customerNotified']
            ?? request()->input('customerNotified')
            ?? request()->input('customer_notified')
            ?? false);
    }

    protected function toDto($row): AdminOrderComment
    {
        $model = new AdminOrderComment;
        $model->id = (int) $row->id;
        $model->orderId = (int) $row->order_id;
        $model->comment = $row->comment;
        $model->customerNotified = (bool) $row->customer_notified;
        $model->createdAt = $row->created_at ? (string) $row->created_at : null;
        $model->updatedAt = $row->updated_at ? (string) $row->updated_at : null;

        return $model;
    }
}
