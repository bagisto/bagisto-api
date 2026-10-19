<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingEvent;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Marketing\Models\Event;

class AdminMarketingEventItemProvider extends AbstractAdminItemProvider
{
    /**
     * Map to dto public.
     */
    public function mapToDtoPublic(object $event): AdminMarketingEvent
    {
        return $this->mapToDto($event);
    }

    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.event.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return Event::find($id);
    }

    /**
     * Map to dto.
     */
    protected function mapToDto(object $event): AdminMarketingEvent
    {
        $dto = new AdminMarketingEvent;

        $dto->id = (int) $event->id;
        $dto->name = $event->name;
        $dto->description = $event->description;
        $dto->date = $event->date ? (string) $event->date : null;
        $dto->createdAt = $event->created_at?->toIso8601String();
        $dto->updatedAt = $event->updated_at?->toIso8601String();

        return $dto;
    }
}
