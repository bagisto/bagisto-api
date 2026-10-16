<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminNotification;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Notification\Repositories\NotificationRepository;

/**
 * The admin notification bell, mirroring NotificationController::getNotifications and
 * NotificationRepository::getParamsData. Bespoke rather than the abstract collection base
 * because the listing carries status counts and the unread total next to the page.
 */
class AdminNotificationProvider implements ProviderInterface
{
    protected const DEFAULT_PER_PAGE = 10;

    protected const MAX_PER_PAGE = 50;

    public function __construct(protected NotificationRepository $notificationRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        AdminReadPermissions::authorize();

        $args = $context['args'] ?? request()->query();

        [$page, $perPage] = $this->resolvePaging($args);

        $query = $this->notificationRepository->getModel()->newQuery()->with('order');

        if (! empty($args['status']) && $args['status'] !== 'All') {
            $query->whereHas('order', fn ($order) => $order->where('status', $args['status']));
        }

        if (isset($args['read']) && $args['read'] !== '') {
            $query->where('read', (int) (bool) $args['read']);
        }

        $total = (clone $query)->count();

        $items = $query->latest()
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn ($notification) => $this->toResource($notification))
            ->all();

        return new AdminNotificationPaginator(
            new LengthAwarePaginator($items, $total, $perPage, $page, ['path' => request()->url()]),
            ['statusCounts' => $this->statusCounts(), 'totalUnread' => $this->totalUnread()],
        );
    }

    /**
     * One notification by id, for the GraphQL item query.
     */
    public function find(int $id): AdminNotification
    {
        AdminReadPermissions::authorize();

        $notification = $this->notificationRepository->getModel()->newQuery()->with('order')->find($id);

        if (! $notification) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.notification.not-found'));
        }

        return $this->toResource($notification);
    }

    /**
     * Unread total and per-order-status counts, for the GraphQL bell badge.
     */
    public function summary(): AdminNotification
    {
        AdminReadPermissions::authorize();

        $result = new AdminNotification;
        $result->id = 0;
        $result->total_unread = $this->totalUnread();
        $result->status_counts = $this->statusCounts();

        return $result;
    }

    public function totalUnread(): int
    {
        return $this->notificationRepository->where('read', 0)->count();
    }

    /**
     * Notification count per order status, as core's repository computes it.
     */
    public function statusCounts(): array
    {
        return $this->notificationRepository->getModel()->newQuery()
            ->join('orders', 'notifications.order_id', '=', 'orders.id')
            ->select('orders.status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('orders.status')
            ->get()
            ->map(fn ($row) => ['status' => $row->status, 'statusCount' => (int) $row->status_count])
            ->all();
    }

    public function toResource(object $notification): AdminNotification
    {
        $order = $notification->order;

        $resource = new AdminNotification;
        $resource->id = (int) $notification->id;
        $resource->order_id = (int) $notification->order_id;
        $resource->type = $notification->type;
        $resource->read = (bool) $notification->read;
        $resource->order_increment_id = $order?->increment_id;
        $resource->order_status = $order?->status;
        $resource->order_status_label = $order ? ($order->status_label ?? $order->status) : null;
        $resource->created_at = $notification->created_at?->toIso8601String();
        $resource->updated_at = $notification->updated_at?->toIso8601String();

        return $resource;
    }

    /**
     * Resolve [page, perPage] from REST page/per_page (or core's `limit`) or GraphQL first/after.
     */
    protected function resolvePaging(array $args): array
    {
        $perPage = (int) ($args['first'] ?? $args['per_page'] ?? $args['limit'] ?? static::DEFAULT_PER_PAGE);

        if ($perPage <= 0) {
            $perPage = static::DEFAULT_PER_PAGE;
        }

        $perPage = min($perPage, static::MAX_PER_PAGE);

        if (! empty($args['after'])) {
            $decoded = base64_decode((string) $args['after'], true);

            if ($decoded !== false && ctype_digit($decoded)) {
                return [(int) floor(((int) $decoded + 1) / $perPage) + 1, $perPage];
            }
        }

        return [max(1, (int) ($args['page'] ?? 1)), $perPage];
    }
}
