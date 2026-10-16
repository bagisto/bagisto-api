<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Webkul\BagistoApi\Admin\Dto\AdminNotificationReadInput;
use Webkul\BagistoApi\Admin\Models\AdminNotification;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Notification\Repositories\NotificationRepository;

/**
 * Mark notifications read, mirroring NotificationController::viewedNotifications and
 * ::readAllNotifications.
 */
class AdminNotificationProcessor implements ProcessorInterface
{
    public function __construct(
        protected NotificationRepository $notificationRepository,
        protected AdminNotificationProvider $provider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        AdminReadPermissions::authorize();

        $readAll = $operation instanceof Mutation
            ? $operation->getName() === 'readAll'
            : str_ends_with((string) $operation->getUriTemplate(), '/read-all');

        $result = $readAll
            ? $this->readAll()
            : $this->readOne($this->resolveOrderId($data, $uriVariables, $context));

        if ($operation instanceof Mutation) {
            return $result;
        }

        return new JsonResponse($readAll
            ? ['totalUnread' => $result->total_unread, 'message' => $result->message]
            : $this->toArray($result), 200);
    }

    protected function readOne(int $orderId): AdminNotification
    {
        $notification = $orderId > 0
            ? $this->notificationRepository->findOneWhere(['order_id' => $orderId])
            : null;

        if (! $notification) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.notification.not-found'));
        }

        $notification->read = 1;
        $notification->save();

        return $this->provider->toResource($notification);
    }

    protected function readAll(): AdminNotification
    {
        $this->notificationRepository->where('read', 0)->update(['read' => 1]);

        $result = new AdminNotification;
        $result->id = 0;
        $result->total_unread = $this->provider->totalUnread();
        $result->message = __('bagistoapi::app.admin.notification.marked-success');

        return $result;
    }

    protected function toArray(AdminNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'orderId' => $notification->order_id,
            'type' => $notification->type,
            'read' => $notification->read,
            'orderIncrementId' => $notification->order_increment_id,
            'orderStatus' => $notification->order_status,
            'orderStatusLabel' => $notification->order_status_label,
            'createdAt' => $notification->created_at,
            'updatedAt' => $notification->updated_at,
        ];
    }

    protected function resolveOrderId(mixed $data, array $uriVariables, array $context): int
    {
        if ($data instanceof AdminNotificationReadInput && $data->order_id) {
            return (int) $data->order_id;
        }

        return (int) ($uriVariables['orderId']
            ?? $context['args']['input']['orderId']
            ?? request()->route('orderId')
            ?? 0);
    }
}
