<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchTermUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Marketing\Models\SearchTerm;

class AdminMarketingSearchTermProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminMarketingSearchTermItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // GraphQL delete
        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingSearchTermUpdateInput) {
            $this->assertPermission($admin, 'marketing.search_seo.search_terms.delete');
            $id = (int) basename((string) ($data->id ?? ($context['args']['input']['id'] ?? '')));

            return $this->handleDelete($id);
        }

        // Update — DTO (GraphQL) or resource (REST PUT)
        if ($data instanceof AdminMarketingSearchTermUpdateInput
            || ($data instanceof AdminMarketingSearchTerm && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.search_seo.search_terms.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) ($data->id ?? ($context['args']['input']['id'] ?? ''))));
            $payload = $this->resolvePayload($data, $context, $isGraphQL);

            return $this->handleUpdate($id, $payload);
        }

        // REST DELETE
        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.search_seo.search_terms.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleUpdate(int $id, array $payload): AdminMarketingSearchTerm
    {
        $existing = SearchTerm::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.search-term.not-found'));
        }

        if (! array_key_exists('term', $payload) || $payload['term'] === null || $payload['term'] === '') {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.search-term.term-required'), 422);
        }

        if (! empty($payload['redirect_url']) && ! filter_var($payload['redirect_url'], FILTER_VALIDATE_URL)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.search-term.redirect-url-invalid'), 422);
        }

        Event::dispatch('marketing.search_seo.search_terms.update.before', $id);

        $updateData = ['term' => $payload['term']];
        if (array_key_exists('redirect_url', $payload)) {
            $updateData['redirect_url'] = $payload['redirect_url'] ?: null;
        }
        $existing->update($updateData);

        Event::dispatch('marketing.search_seo.search_terms.update.after', $existing);

        $fresh = SearchTerm::find($id);

        return $this->itemProvider->mapToDto($fresh);
    }

    protected function handleDelete(int $id): array
    {
        $existing = SearchTerm::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.search-term.not-found'));
        }

        Event::dispatch('marketing.search_seo.search_terms.delete.before', $id);
        $existing->delete();
        Event::dispatch('marketing.search_seo.search_terms.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.marketing.search-term.deleted')];
    }

    protected function resolvePayload(mixed $data, array $context, bool $isGraphQL): array
    {
        $payload = [];

        if ($data instanceof AdminMarketingSearchTermUpdateInput) {
            if ($data->term !== null) {
                $payload['term'] = $data->term;
            }
            if ($data->redirect_url !== null) {
                $payload['redirect_url'] = $data->redirect_url;
            }
        }

        if ($isGraphQL) {
            $args = $context['args']['input'] ?? $context['args'] ?? [];
            foreach (['term' => 'term', 'redirect_url' => 'redirect_url', 'redirectUrl' => 'redirect_url'] as $k => $target) {
                if (array_key_exists($k, $args)) {
                    $payload[$target] = $args[$k];
                }
            }

            return $payload;
        }

        $body = request()->all();
        foreach (['term' => 'term', 'redirect_url' => 'redirect_url', 'redirectUrl' => 'redirect_url'] as $k => $target) {
            if (array_key_exists($k, $body)) {
                $payload[$target] = $body[$k];
            }
        }

        return $payload;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.search-term.no-permission'));
        }

        if (($role->permission_type ?? null) === 'all') {
            return;
        }

        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }

        if (! in_array($permission, $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.search-term.no-permission'));
        }
    }
}
