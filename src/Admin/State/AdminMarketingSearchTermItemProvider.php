<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchTermRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\Channel;
use Webkul\Marketing\Models\SearchTerm;

/**
 * Search term detail — GET /api/admin/marketing/search-terms/{id} +
 * adminMarketingSearchTerm.
 *
 * Branches: GraphQL → the AdminMarketingSearchTerm Eloquent model (the `channel`
 * to-one object resolves); REST → the flat AdminMarketingSearchTermRestDto with
 * `channel` as an object `{ id, code, name }`.
 */
class AdminMarketingSearchTermItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminMarketingSearchTerm|AdminMarketingSearchTermRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.search-term.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = AdminMarketingSearchTerm::with(['channel'])->find($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.search-term.not-found'));
            }

            return $model;
        }

        $term = SearchTerm::find($id);

        if (! $term) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.search-term.not-found'));
        }

        return $this->buildRestDto($term);
    }

    public function buildRestDtoPublic(object $term): AdminMarketingSearchTermRestDto
    {
        return $this->buildRestDto($term);
    }

    protected function buildRestDto(object $term): AdminMarketingSearchTermRestDto
    {
        /** @var SearchTerm $term */
        $dto = new AdminMarketingSearchTermRestDto;

        $dto->id = (int) $term->id;
        $dto->term = $term->term;
        $dto->results = $term->results !== null ? (int) $term->results : null;
        $dto->uses = $term->uses !== null ? (int) $term->uses : null;
        $dto->redirectUrl = $term->redirect_url;
        $dto->locale = $term->locale;
        $dto->channel = $this->channelObject($term->channel_id);
        $dto->createdAt = $term->created_at?->toIso8601String();
        $dto->updatedAt = $term->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * @return array{id:int, code:string|null, name:string|null}|null
     */
    protected function channelObject(?int $channelId): ?array
    {
        if (! $channelId) {
            return null;
        }

        try {
            $channel = Channel::find($channelId);
        } catch (\Throwable $e) {
            $channel = null;
        }

        if (! $channel) {
            return null;
        }

        return [
            'id' => (int) $channel->id,
            'code' => $channel->code,
            'name' => $channel->name,
        ];
    }
}
