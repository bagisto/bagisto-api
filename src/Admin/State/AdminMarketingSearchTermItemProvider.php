<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Marketing\Models\SearchTerm;

class AdminMarketingSearchTermItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.search-term.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return SearchTerm::find($id);
    }

    public function mapToDto(object $entity): object
    {
        return $this->doMap($entity);
    }

    protected function doMap(SearchTerm $s): AdminMarketingSearchTerm
    {
        $dto = new AdminMarketingSearchTerm;
        $dto->id = (int) $s->id;
        $dto->term = $s->term;
        $dto->results = $s->results !== null ? (int) $s->results : null;
        $dto->uses = $s->uses !== null ? (int) $s->uses : null;
        $dto->redirectUrl = $s->redirect_url;
        $dto->channelId = $s->channel_id !== null ? (int) $s->channel_id : null;

        try {
            $dto->channelName = $s->channel_id
                ? optional(\Webkul\Core\Models\Channel::find($s->channel_id))->code
                : null;
        } catch (\Throwable $e) {
            $dto->channelName = null;
        }

        $dto->locale = $s->locale;
        $dto->createdAt = $s->created_at?->toIso8601String();
        $dto->updatedAt = $s->updated_at?->toIso8601String();

        return $dto;
    }
}
