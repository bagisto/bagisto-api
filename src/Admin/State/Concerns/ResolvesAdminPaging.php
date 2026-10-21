<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

trait ResolvesAdminPaging
{
    /**
     * Resolve [page, perPage] from REST query params or GraphQL args (first/after).
     * Cursor `after` is the API Platform convention: base64-encoded 0-based offset.
     */
    protected function resolvePaging(array $args): array
    {
        $first = $args['first'] ?? null;
        $perPage = $first !== null
            ? (int) $first
            : (int) ($args['per_page'] ?? static::DEFAULT_PER_PAGE);

        if ($perPage <= 0) {
            $perPage = static::DEFAULT_PER_PAGE;
        }

        $perPage = min($perPage, static::MAX_PER_PAGE);

        if (! empty($args['after'])) {
            $decoded = base64_decode((string) $args['after'], true);
            if (
                $decoded !== false
                && ctype_digit($decoded)
            ) {
                $offset = (int) $decoded + 1;
                $page = (int) floor($offset / $perPage) + 1;

                return [$page, $perPage];
            }
        }

        $page = max(1, (int) ($args['page'] ?? 1));

        return [$page, $perPage];
    }
}
