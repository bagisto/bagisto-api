<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingUrlRewrite;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Marketing\Models\URLRewrite;

class AdminMarketingUrlRewriteItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.url-rewrite.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return URLRewrite::find($id);
    }

    protected function mapToDto(object $rewrite): AdminMarketingUrlRewrite
    {
        /** @var URLRewrite $rewrite */
        $dto = new AdminMarketingUrlRewrite;

        $dto->id = (int) $rewrite->id;
        $dto->entityType = $rewrite->entity_type;
        $dto->requestPath = $rewrite->request_path;
        $dto->targetPath = $rewrite->target_path;
        $dto->redirectType = $rewrite->redirect_type;
        $dto->locale = $rewrite->locale;
        $dto->createdAt = $rewrite->created_at?->toIso8601String();
        $dto->updatedAt = $rewrite->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * Public alias used by the processor to reuse the mapping logic.
     */
    public function mapToDtoPublic(object $rewrite): AdminMarketingUrlRewrite
    {
        return $this->mapToDto($rewrite);
    }
}
