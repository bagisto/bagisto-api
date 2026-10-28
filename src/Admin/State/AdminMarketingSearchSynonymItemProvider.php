<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchSynonym;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Marketing\Models\SearchSynonym;

class AdminMarketingSearchSynonymItemProvider extends AbstractAdminItemProvider
{
    /**
     * Map to dto public.
     */
    public function mapToDtoPublic(object $synonym): AdminMarketingSearchSynonym
    {
        return $this->mapToDto($synonym);
    }

    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.search-synonym.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return SearchSynonym::find($id);
    }

    /**
     * Map to dto.
     */
    protected function mapToDto(object $synonym): AdminMarketingSearchSynonym
    {
        $dto = new AdminMarketingSearchSynonym;

        $dto->id = (int) $synonym->id;
        $dto->name = $synonym->name;
        $dto->terms = $synonym->terms;
        $dto->createdAt = $synonym->created_at?->toIso8601String();
        $dto->updatedAt = $synonym->updated_at?->toIso8601String();

        return $dto;
    }
}
