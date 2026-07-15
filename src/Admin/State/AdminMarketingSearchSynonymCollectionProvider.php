<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchSynonym;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/search-synonyms + adminMarketingSearchSynonyms.
 *
 * Filters: name (LIKE), terms (LIKE).
 * Sort:    id (default desc), name.
 */
class AdminMarketingSearchSynonymCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('search_synonyms')->select(
            'search_synonyms.id',
            'search_synonyms.name',
            'search_synonyms.terms',
            'search_synonyms.created_at',
            'search_synonyms.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('search_synonyms.name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['terms'])) {
            $query->where('search_synonyms.terms', 'like', '%'.$args['terms'].'%');
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id' => 'search_synonyms.id',
            'name' => 'search_synonyms.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'search_synonyms.id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingSearchSynonym
    {
        $dto = new AdminMarketingSearchSynonym;

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->terms = $row->terms;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
