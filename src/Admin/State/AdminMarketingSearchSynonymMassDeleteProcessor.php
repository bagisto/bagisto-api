<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchSynonymMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchSynonymMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Marketing\Models\SearchSynonym;
use Webkul\Marketing\Repositories\SearchSynonymRepository;

/**
 * POST /api/admin/marketing/search-synonyms/mass-delete
 *      + createAdminMarketingSearchSynonymMassDelete.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SearchSynonymController::massDestroy:
 *   - Iterate indices, fire before/after events per id, delete each one.
 *   - Non-existent IDs are silently skipped (mirrors monolith).
 */
class AdminMarketingSearchSynonymMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(protected SearchSynonymRepository $searchSynonymRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'marketing.search_seo.search_synonyms.delete');

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.search-synonym.indices-required'), 422);
        }

        $deleted = [];

        foreach ($indices as $index) {
            $id = (int) $index;
            $synonym = SearchSynonym::find($id);
            if (! $synonym) {
                continue;
            }

            try {
                Event::dispatch('marketing.search_seo.search_synonyms.delete.before', $id);

                $this->searchSynonymRepository->delete($id);

                Event::dispatch('marketing.search_seo.search_synonyms.delete.after', $id);

                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                throw new InvalidInputException(__('bagistoapi::app.admin.marketing.search-synonym.delete-failed'), 500);
            }
        }

        $result = new AdminMarketingSearchSynonymMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.marketing.search-synonym.mass-deleted');

        return $result;
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

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminMarketingSearchSynonymMassDeleteInput && ! empty($data->indices)) {
            return $data->indices;
        }

        $fromArgs = $context['args']['input']['indices']
            ?? $context['args']['indices']
            ?? null;

        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $fromBody = request()->input('indices');
        if (is_array($fromBody)) {
            return $fromBody;
        }

        return [];
    }
}
