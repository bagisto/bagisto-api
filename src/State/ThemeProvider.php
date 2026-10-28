<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Theme;
use Webkul\Theme\Models\Section;

class ThemeProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [self::build()];
    }

    /**
     * Build.
     */
    public static function build(): Theme
    {
        $channel = core()->getCurrentChannel();

        $code = $channel->theme ?: config('themes.shop-default');

        $theme = new Theme;

        $theme->code = $code;
        $theme->name = config('themes.shop.'.$code.'.name') ?? $code;
        $theme->section_types = Section::TYPES;

        return $theme;
    }
}
