<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchTermRestDto;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

class AdminMarketingSearchTermCollectionProvider extends AbstractAdminCollectionProvider
{
    /**
     * The listing is graph ql.
     */
    protected bool $listingIsGraphQL = false;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

    /**
     * Get sortable.
     */
    protected function getSortable(): array
    {
        return ['id', 'term', 'uses', 'results'];
    }

    /**
     * Build query.
     */
    protected function buildQuery(array $args)
    {
        return DB::table('search_terms')->select(
            'search_terms.id',
            'search_terms.term',
            'search_terms.results',
            'search_terms.uses',
            'search_terms.redirect_url',
            'search_terms.display_in_suggested_terms',
            'search_terms.channel_id',
            'search_terms.locale',
            'search_terms.created_at',
            'search_terms.updated_at',
        );
    }

    /**
     * Apply filters.
     */
    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['term'])) {
            $query->where('search_terms.term', db_grammar()->caseInsensitiveLike(), '%'.$args['term'].'%');
        }

        if (
            isset($args['channel_id'])
            && $args['channel_id'] !== ''
            && $args['channel_id'] !== null
        ) {
            $query->where('search_terms.channel_id', (int) $args['channel_id']);
        }

        if (! empty($args['locale'])) {
            $query->where('search_terms.locale', (string) $args['locale']);
        }
    }

    /**
     * Apply sort.
     */
    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = [
            'id' => 'search_terms.id',
            'term' => 'search_terms.term',
            'uses' => 'search_terms.uses',
            'results' => 'search_terms.results',
        ];

        $query->orderBy($map[$column] ?? 'search_terms.id', $direction);
    }

    /**
     * Map row.
     */
    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminMarketingSearchTermRestDto;
        $dto->id = (int) $row->id;
        $dto->term = $row->term;
        $dto->results = $row->results !== null ? (int) $row->results : null;
        $dto->uses = $row->uses !== null ? (int) $row->uses : null;
        $dto->redirectUrl = $row->redirect_url;
        $dto->locale = $row->locale;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminMarketingSearchTerm. The `channel`
     * to-one is set null (detail-only — no per-row N+1).
     */
    protected function mapRowToEloquent(object $row): AdminMarketingSearchTerm
    {
        $model = (new AdminMarketingSearchTerm)->forceFill([
            'id' => (int) $row->id,
            'term' => $row->term,
            'results' => $row->results,
            'uses' => $row->uses,
            'redirect_url' => $row->redirect_url,
            'display_in_suggested_terms' => $row->display_in_suggested_terms,
            'locale' => $row->locale,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $model->setRelation('channel', null);

        return $model;
    }
}
