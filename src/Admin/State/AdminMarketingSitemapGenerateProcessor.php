<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Carbon\Carbon;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSitemapGenerateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSitemapGenerate;
use Webkul\BagistoApi\Admin\State\Concerns\MapsSitemapChannels;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sitemap\Jobs\ProcessSitemap;
use Webkul\Sitemap\Models\Sitemap;

/**
 * Handles POST /api/admin/marketing/sitemaps/{id}/generate +
 * createAdminMarketingSitemapGenerate.
 */
class AdminMarketingSitemapGenerateProcessor implements ProcessorInterface
{
    use MapsSitemapChannels;

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'marketing.search_seo.sitemaps.edit');

        $id = $this->resolveSitemapId($data, $uriVariables, $context);
        if (! $id) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.sitemap.generate.id-required'), 422);
        }

        $sitemap = Sitemap::with('channels')->find($id);
        if (! $sitemap) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.sitemap.not-found'));
        }

        if ($sitemap->channels->isEmpty()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.sitemap.generate.no-channels'), 422);
        }

        try {
            ProcessSitemap::dispatchSync($sitemap);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.sitemap.generate.failed', ['message' => $e->getMessage()]),
                500,
            );
        }

        $sitemap = Sitemap::with('channels')->find($id);
        $additional = $sitemap->additional ?? [];

        $result = new AdminMarketingSitemapGenerate;
        $result->id = (int) $sitemap->id;
        $result->sitemapId = (int) $sitemap->id;
        $result->generatedFiles = $this->sitemapGeneratedFiles($additional, $sitemap->channels);
        $result->urls = $this->sitemapUrls($sitemap->channels, (int) $sitemap->id, $sitemap->path, $sitemap->file_name);
        $result->indexFile = $additional['index'] ?? null;
        $result->generatedSitemaps = $additional['sitemaps'] ?? [];
        $result->generatedAt = $sitemap->generated_at ? Carbon::parse($sitemap->generated_at)->toIso8601String() : null;
        $result->message = __('bagistoapi::app.admin.marketing.sitemap.generate.success');

        return $result;
    }

    protected function resolveSitemapId(mixed $data, array $uriVariables, array $context): int
    {
        if (! empty($uriVariables['id'])) {
            return (int) $uriVariables['id'];
        }

        if ($data instanceof AdminMarketingSitemapGenerateInput && $data->sitemapId) {
            return (int) $data->sitemapId;
        }

        $fromArgs = $context['args']['input']['sitemapId']
            ?? $context['args']['sitemapId']
            ?? null;
        if ($fromArgs) {
            return (int) $fromArgs;
        }

        $iri = $context['args']['input']['id'] ?? $context['args']['id'] ?? null;
        if ($iri) {
            return (int) basename((string) $iri);
        }

        $routeId = request()->route('id');
        if ($routeId) {
            return (int) $routeId;
        }

        return (int) (request()->input('sitemapId') ?? request()->input('sitemap_id') ?? 0);
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.sitemap.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.sitemap.no-permission'));
        }
    }
}
