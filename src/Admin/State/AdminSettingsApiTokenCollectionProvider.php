<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\BagistoApi\Admin\Services\DomainBinding;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAdminApiToken;
use Webkul\BagistoApi\Admin\State\Concerns\ResolvesAdminPaging;

class AdminSettingsApiTokenCollectionProvider implements ProviderInterface
{
    use ChecksAdminPermission;
    use MapsAdminApiToken;
    use ResolvesAdminPaging;

    /**
     * The page size when none is asked for.
     */
    protected const DEFAULT_PER_PAGE = 10;

    /**
     * The largest page size.
     */
    protected const MAX_PER_PAGE = 50;

    /**
     * Create a new API token collection provider.
     */
    public function __construct(
        protected AdminApiTokenRepository $apiTokenRepository,
        protected AdminApiTokenIssuer $issuer,
        protected DomainBinding $domainBinding,
    ) {}

    /**
     * A page of API tokens, newest first: every admin's with manage_others, otherwise the caller's own.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $actor = $this->authorizedAdmin('settings.api_tokens', 'bagistoapi::app.admin.api-tokens.errors.no-permission');

        $args = $context['args'] ?? request()->query();

        [$page, $perPage] = $this->resolvePaging($args);

        $adminId = $this->issuer->canManageOthers($actor)
            ? $this->adminFilter($args)
            : (int) $actor->id;

        $paginator = $this->apiTokenRepository->paginateFiltered(
            $adminId,
            $this->statusFilter($args),
            $this->domainFilter($args),
            $page,
            $perPage,
        );

        $paginator->setCollection($paginator->getCollection()->map(
            fn (AdminApiToken $token) => $this->mapApiToken($token)
        ));

        return new Paginator($paginator);
    }

    /**
     * The admin filter, or null for every admin.
     */
    protected function adminFilter(array $args): ?int
    {
        $adminId = (int) ($args['admin_id'] ?? $args['adminId'] ?? 0);

        return $adminId > 0
            ? $adminId
            : null;
    }

    /**
     * The status filter, ignored when it is not a known status.
     */
    protected function statusFilter(array $args): ?string
    {
        $status = $args['status'] ?? null;

        return in_array($status, AdminApiToken::STATUSES, true)
            ? $status
            : null;
    }

    /**
     * The domain filter, normalised as stored, or null when none or an unusable one is given.
     */
    protected function domainFilter(array $args): ?string
    {
        $domain = $args['domain'] ?? null;

        return is_string($domain)
            ? $this->domainBinding->normalize($domain)
            : null;
    }
}
