<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchSynonym;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Marketing\Models\SearchSynonym;

class AdminMarketingSearchSynonymItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.search-synonym.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return SearchSynonym::find($id);
    }

    protected function mapToDto(object $synonym): AdminMarketingSearchSynonym
    {
        /** @var SearchSynonym $synonym */
        $dto = new AdminMarketingSearchSynonym;

        $dto->id = (int) $synonym->id;
        $dto->name = $synonym->name;
        $dto->terms = $synonym->terms;
        $dto->createdAt = $synonym->created_at?->toIso8601String();
        $dto->updatedAt = $synonym->updated_at?->toIso8601String();

        return $dto;
    }

    public function mapToDtoPublic(object $synonym): AdminMarketingSearchSynonym
    {
        return $this->mapToDto($synonym);
    }
}
