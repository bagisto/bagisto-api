<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingUrlRewriteMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingUrlRewriteMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Marketing\Models\URLRewrite;
use Webkul\Marketing\Repositories\URLRewriteRepository;

/**
 * POST /api/admin/marketing/url-rewrites/mass-delete + createAdminMarketingUrlRewriteMassDelete.
 *
 * Mirrors URLRewriteController::massDestroy — iterate indices, fire
 * before/after events per id, delete each one. Non-existent IDs silently
 * skipped.
 */
class AdminMarketingUrlRewriteMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(protected URLRewriteRepository $urlRewriteRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'marketing.search_seo.url_rewrites.delete');

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.url-rewrite.indices-required'), 422);
        }

        $deleted = [];

        foreach ($indices as $index) {
            $id = (int) $index;
            $rewrite = URLRewrite::find($id);
            if (! $rewrite) {
                continue;
            }

            try {
                Event::dispatch('marketing.search_seo.url_rewrites.delete.before', $id);

                $this->urlRewriteRepository->delete($id);

                Event::dispatch('marketing.search_seo.url_rewrites.delete.after', $id);

                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                throw new InvalidInputException(__('bagistoapi::app.admin.marketing.url-rewrite.delete-failed'), 500);
            }
        }

        $result = new AdminMarketingUrlRewriteMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.marketing.url-rewrite.mass-deleted');

        return $result;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.url-rewrite.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.url-rewrite.no-permission'));
        }
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminMarketingUrlRewriteMassDeleteInput && ! empty($data->indices)) {
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
