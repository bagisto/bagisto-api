<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchSynonymCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchSynonymUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchSynonym;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Marketing\Models\SearchSynonym;
use Webkul\Marketing\Repositories\SearchSynonymRepository;

/**
 * Handles POST, PUT, DELETE on AdminMarketingSearchSynonym.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SearchSynonymController:
 *   store / update / destroy. Events fired:
 *     marketing.search_seo.search_synonyms.create.before / after
 *     marketing.search_seo.search_synonyms.update.before / after
 *     marketing.search_seo.search_synonyms.delete.before / after
 *
 * Permission resolution: Sanctum pattern — read role->permission_type /
 * role->permissions directly. Never calls bouncer().
 *
 * Validation mirrors SearchSynonymController:
 *   - name  required
 *   - terms required
 */
class AdminMarketingSearchSynonymProcessor implements ProcessorInterface
{
    public function __construct(
        protected SearchSynonymRepository $searchSynonymRepository,
        protected AdminMarketingSearchSynonymItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingSearchSynonymUpdateInput) {
            $this->assertPermission($admin, 'marketing.search_seo.search_synonyms.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminMarketingSearchSynonymCreateInput
            || ($data instanceof AdminMarketingSearchSynonym && $operation instanceof Post)) {
            $this->assertPermission($admin, 'marketing.search_seo.search_synonyms.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminMarketingSearchSynonymUpdateInput
            || ($data instanceof AdminMarketingSearchSynonym && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.search_seo.search_synonyms.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.search_seo.search_synonyms.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminMarketingSearchSynonym
    {
        $this->validatePayload($input);

        Event::dispatch('marketing.search_seo.search_synonyms.create.before');

        $synonym = $this->searchSynonymRepository->create([
            'name' => $input['name'],
            'terms' => $input['terms'],
        ]);

        $synonym = SearchSynonym::find($synonym->id);

        Event::dispatch('marketing.search_seo.search_synonyms.create.after', $synonym);

        return $this->itemProvider->mapToDtoPublic($synonym);
    }

    protected function handleUpdate(int $id, array $input): AdminMarketingSearchSynonym
    {
        $synonym = SearchSynonym::find($id);
        if (! $synonym) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.search-synonym.not-found'));
        }

        $this->validatePayload($input);

        Event::dispatch('marketing.search_seo.search_synonyms.update.before', $id);

        $this->searchSynonymRepository->update([
            'name' => $input['name'],
            'terms' => $input['terms'],
        ], $id);

        $synonym = SearchSynonym::find($id);

        Event::dispatch('marketing.search_seo.search_synonyms.update.after', $synonym);

        return $this->itemProvider->mapToDtoPublic($synonym);
    }

    protected function handleDelete(int $id): array
    {
        $synonym = SearchSynonym::find($id);
        if (! $synonym) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.search-synonym.not-found'));
        }

        Event::dispatch('marketing.search_seo.search_synonyms.delete.before', $id);

        try {
            $this->searchSynonymRepository->delete($id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.search-synonym.delete-failed'),
                500,
            );
        }

        Event::dispatch('marketing.search_seo.search_synonyms.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.marketing.search-synonym.deleted')];
    }

    protected function validatePayload(array $input): void
    {
        $rules = [
            'name' => ['required', 'string'],
            'terms' => ['required', 'string'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.search-synonym.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.search-synonym.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingSearchSynonymCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminMarketingSearchSynonymUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingSearchSynonymUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function normaliseArgs(array $input): array
    {
        unset($input['id']);

        return $input;
    }

    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result[$key] = $value;
        }

        foreach (get_object_vars($dto) as $key => $value) {
            if ($value !== null && ! array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
