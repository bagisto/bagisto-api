<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingTemplate;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Marketing\Models\Template;

class AdminMarketingTemplateItemProvider extends AbstractAdminItemProvider
{
    /**
     * Public alias used by the processor to reuse the mapping logic.
     */
    public function mapToDtoPublic(object $template): AdminMarketingTemplate
    {
        return $this->mapToDto($template);
    }

    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.template.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return Template::find($id);
    }

    /**
     * Map to dto.
     */
    protected function mapToDto(object $template): AdminMarketingTemplate
    {
        $dto = new AdminMarketingTemplate;

        $dto->id = (int) $template->id;
        $dto->name = $template->name;
        $dto->status = $template->status;
        $dto->content = $template->content;
        $dto->createdAt = $template->created_at?->toIso8601String();
        $dto->updatedAt = $template->updated_at?->toIso8601String();

        return $dto;
    }
}
