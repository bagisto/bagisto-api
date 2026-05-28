<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingTemplate;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Marketing\Models\Template;

class AdminMarketingTemplateItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.template.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Template::find($id);
    }

    protected function mapToDto(object $template): AdminMarketingTemplate
    {
        /** @var Template $template */
        $dto = new AdminMarketingTemplate;

        $dto->id = (int) $template->id;
        $dto->name = $template->name;
        $dto->status = $template->status;
        $dto->content = $template->content;
        $dto->createdAt = $template->created_at?->toIso8601String();
        $dto->updatedAt = $template->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * Public alias used by the processor to reuse the mapping logic.
     */
    public function mapToDtoPublic(object $template): AdminMarketingTemplate
    {
        return $this->mapToDto($template);
    }
}
