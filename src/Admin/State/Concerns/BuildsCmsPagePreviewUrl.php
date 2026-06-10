<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

trait BuildsCmsPagePreviewUrl
{
    protected function buildPreviewUrl(?string $urlKey): ?string
    {
        if (! $urlKey) {
            return null;
        }

        try {
            return route('shop.cms.page', $urlKey);
        } catch (\Throwable) {
            return rtrim((string) config('app.url'), '/').'/page/'.$urlKey;
        }
    }
}
