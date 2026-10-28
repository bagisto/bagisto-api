<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Theme;
use Webkul\Theme\Models\Section;

/**
 * The theme the current channel is drawn with.
 *
 * A channel records its theme on itself, falling back to the shop default when it has
 * never been pointed at one.
 */
class ThemeProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [self::build()];
    }

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
