<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionPreview;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\Theme\Models\Section;
use Webkul\Theme\Repositories\SectionRepository;

/**
 * The editor preview, as data. Reuses the repository's own drafted read so the API and
 * the admin panel resolve a staged theme the same way.
 */
class AdminAppearanceSectionPreviewProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    public function __construct(
        protected SectionRepository $sectionRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->authorizedAdmin('appearance.sections', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args'] ?? [];

        $theme = $this->scope->themeOrFail($this->scope->pathValue($uriVariables, 'code') ?? $args['code'] ?? null);

        $channel = $this->scope->channel($args);

        $locale = $this->scope->locale($channel, $args)->code;

        $sections = $this->sectionRepository->getDraftedForPreview($channel->id, $theme['code'], $locale);

        $result = new AdminAppearanceSectionPreview;

        $result->theme_code = $theme['code'];
        $result->channel_id = (int) $channel->id;
        $result->locale = $locale;

        $result->sections = collect($sections)
            ->sortBy(fn ($section) => $section->type === Section::FOOTER_LINKS ? 1 : 0)
            ->map(fn ($section) => [
                'id' => (int) $section->id,
                'name' => $section->name,
                'type' => $section->type,
                'sortOrder' => (int) $section->sort_order,
                'status' => (bool) $section->status,
                'hasDraft' => $this->sectionRepository->hasDraft($section),
                'options' => $section->translate($locale)?->options,
            ])
            ->values()
            ->all();

        return $result;
    }
}
