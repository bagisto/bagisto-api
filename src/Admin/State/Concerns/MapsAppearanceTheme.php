<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Models\AdminAppearanceTheme;

trait MapsAppearanceTheme
{
    /**
     * A ThemeCatalog row, shaped for the API.
     */
    protected function toThemeResource(array $row): AdminAppearanceTheme
    {
        $theme = new AdminAppearanceTheme;

        $theme->code = $row['code'] ?? null;
        $theme->name = $row['name'] ?? null;
        $theme->author = $row['author'] ?? null;
        $theme->version = $row['version'] ?? null;
        $theme->url = $row['url'] ?? null;
        $theme->demo_url = $row['demo_url'] ?? null;
        $theme->screenshot = $row['screenshot'] ?? null;
        $theme->rating = isset($row['rating']) ? (string) $row['rating'] : null;
        $theme->tags = (array) ($row['tags'] ?? []);
        $theme->description = $row['description'] ?? null;
        $theme->is_installed = (bool) ($row['is_installed'] ?? false);
        $theme->status = $row['status'] ?? null;
        $theme->active_on = array_values((array) ($row['active_on'] ?? []));

        return $theme;
    }
}
