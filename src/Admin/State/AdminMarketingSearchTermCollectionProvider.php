<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * GET /api/admin/marketing/search-terms + adminMarketingSearchTerms GraphQL.
 *
 * Filters: term (LIKE), channel_id, locale.
 * Sort: id (default desc), term, uses, results.
 */
class AdminMarketingSearchTermCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'term', 'uses', 'results'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('search_terms')
            ->leftJoin('channels', 'search_terms.channel_id', '=', 'channels.id')
            ->select(
                'search_terms.id',
                'search_terms.term',
                'search_terms.results',
                'search_terms.uses',
                'search_terms.redirect_url',
                'search_terms.channel_id',
                'channels.code as channel_code',
                'search_terms.locale',
                'search_terms.created_at',
                'search_terms.updated_at',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['term'])) {
            $query->where('search_terms.term', 'like', '%'.$args['term'].'%');
        }

        if (isset($args['channel_id']) && $args['channel_id'] !== '' && $args['channel_id'] !== null) {
            $query->where('search_terms.channel_id', (int) $args['channel_id']);
        }

        if (! empty($args['locale'])) {
            $query->where('search_terms.locale', (string) $args['locale']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = [
            'id'      => 'search_terms.id',
            'term'    => 'search_terms.term',
            'uses'    => 'search_terms.uses',
            'results' => 'search_terms.results',
        ];

        $query->orderBy($map[$column] ?? 'search_terms.id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingSearchTerm
    {
        $dto = new AdminMarketingSearchTerm;
        $dto->id = (int) $row->id;
        $dto->term = $row->term;
        $dto->results = $row->results !== null ? (int) $row->results : null;
        $dto->uses = $row->uses !== null ? (int) $row->uses : null;
        $dto->redirectUrl = $row->redirect_url;
        $dto->channelId = $row->channel_id !== null ? (int) $row->channel_id : null;
        $dto->channelName = $row->channel_code;
        $dto->locale = $row->locale;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
