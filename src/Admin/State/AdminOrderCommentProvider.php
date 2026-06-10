<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Models\AdminOrderComment;
use Webkul\Sales\Models\OrderComment;

/**
 * GET /api/admin/orders/{orderId}/comments + adminOrderComments query.
 *
 * Newest-first cursor list. REST is wrapped in the `{ data, meta }` envelope
 * by AdminCollectionEnvelopeNormalizer; GraphQL uses native cursor pagination.
 */
class AdminOrderCommentProvider implements ProviderInterface
{
    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected Pagination $pagination,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->guard->resolveAdmin();
        $order = $this->guard->resolveOrder($uriVariables, $context, 'orderId');

        $args = $context['args'] ?? [];
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $perPage = $first ?? max(1, (int) (request()->query('per_page', 10)));
        $perPage = min($perPage, 50);

        $offset = 0;
        if ($after = $args['after'] ?? null) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        } else {
            $page = max(1, (int) (request()->query('page', 1)));
            $offset = ($page - 1) * $perPage;
        }

        $query = OrderComment::where('order_id', $order->id)->orderBy('id', 'desc');

        $total = (clone $query)->count();
        $rows = $query->offset($offset)->limit($perPage)->get();

        $items = $rows->map(fn ($row) => $this->toDto($row))->all();

        $page = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator($items, $total, $perPage, $page, ['path' => request()->url()])
        );
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
