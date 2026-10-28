<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAppearanceSection;
use Webkul\Theme\Models\Section;
use Webkul\Theme\Repositories\SectionRepository;

/**
 * Sections of a theme for one channel, in render order with footer links pinned last —
 * the same set the appearance editor lists.
 */
class AdminAppearanceSectionCollectionProvider implements ProviderInterface
{
    use ChecksAdminPermission;
    use MapsAppearanceSection;

    public function __construct(
        protected SectionRepository $sectionRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->authorizedAdmin('appearance.sections', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args'] ?? [];

        $code = $this->scope->pathValue($uriVariables, 'code') ?? $args['code'] ?? null;

        $theme = $this->scope->themeOrFail($code);

        $channel = $this->scope->channel($args);

        $this->scope->locale($channel, $args);

        $sections = Section::query()
            ->with('translations')
            ->where('theme_code', $theme['code'])
            ->where('channel_id', $channel->id)
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn ($section) => $section->type === Section::FOOTER_LINKS ? 1 : 0)
            ->values();

        if ($this->isGraphQL($context)) {
            return $sections
                ->map(fn ($section) => $this->toEloquent($section))
                ->all();
        }

        return $sections
            ->map(fn ($section) => $this->toRestDto($section))
            ->all();
    }
}
