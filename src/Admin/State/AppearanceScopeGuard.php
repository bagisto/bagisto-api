<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Theme\Models\Section;

/**
 * Theme / channel / locale resolution shared by every Appearance endpoint.
 *
 * Mirrors SectionController::themeOrFail / requestedChannel / requestedLocale so the API
 * scopes a request exactly as the appearance editor does.
 */
class AppearanceScopeGuard
{
    /**
     * A path placeholder, wherever API Platform put it: a resource whose identifier is
     * not named `id` gets nothing in $uriVariables, so the live route is read too.
     */
    public function pathValue(array $uriVariables, string $key): ?string
    {
        $value = $uriVariables[$key] ?? request()->route($key);

        return is_null($value) ? null : (string) $value;
    }

    /**
     * The requested theme, or 404 when the installation does not have it.
     */
    public function themeOrFail(?string $code): array
    {
        $theme = $code ? config('themes.shop.'.$code) : null;

        if (! $theme) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.appearance.theme.not-found'));
        }

        return $theme + ['code' => $code];
    }

    /**
     * The channel being edited, falling back to the current one.
     */
    public function channel(array $args = []): object
    {
        $requested = (int) ($args['channel'] ?? request()->input('channel') ?? 0);

        return core()->getAllChannels()->firstWhere('id', $requested)
            ?? core()->getCurrentChannel();
    }

    /**
     * The locale being edited, which has to be one the channel actually runs.
     */
    public function locale(object $channel, array $args = []): object
    {
        $requested = $args['locale'] ?? request()->input('locale');

        $locales = $channel->locales;

        return $locales->firstWhere('code', $requested)
            ?? $locales->firstWhere('code', app()->getLocale())
            ?? $locales->first()
            ?? core()->getCurrentLocale();
    }

    /**
     * The section being acted on, or 404 once it is gone.
     */
    public function sectionOrFail(?int $id): Section
    {
        $section = $id ? Section::find($id) : null;

        if (! $section) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.appearance.section.not-found'));
        }

        return $section;
    }

    /**
     * Section ids of a theme + channel in render order, footer links pinned last.
     */
    public function orderedSectionIds(string $themeCode, int $channelId): array
    {
        return Section::query()
            ->where('theme_code', $themeCode)
            ->where('channel_id', $channelId)
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn ($section) => $section->type === Section::FOOTER_LINKS ? 1 : 0)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * The given order with footer links moved to the end, so a reorder cannot lift the
     * footer out of the bottom of the page.
     */
    public function withPinnedLast(array $sectionIds): array
    {
        $pinned = Section::query()
            ->whereIn('id', $sectionIds)
            ->where('type', Section::FOOTER_LINKS)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $free = array_values(array_diff($sectionIds, $pinned));

        return array_merge($free, array_values(array_intersect($sectionIds, $pinned)));
    }
}
