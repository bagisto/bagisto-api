<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSitemapCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSitemapUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSitemap;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sitemap\Models\Sitemap;
use Webkul\Sitemap\Repositories\SitemapRepository;

/**
 * Handles POST, PUT, DELETE on AdminMarketingSitemap.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SitemapController:
 *   store / update / destroy.
 *
 * Events:
 *   marketing.search_seo.sitemap.create.before / after
 *   marketing.search_seo.sitemap.update.before / after
 *   marketing.search_seo.sitemap.delete.before / after
 *
 * Note: this CRUD endpoint does NOT auto-generate the XML on create/update
 * (the monolith dispatches ProcessSitemap to the queue). Callers must hit
 * POST /generate explicitly so the API stays synchronous and the response
 * carries the resulting file paths.
 */
class AdminMarketingSitemapProcessor implements ProcessorInterface
{
    public function __construct(
        protected SitemapRepository $sitemapRepository,
        protected AdminMarketingSitemapItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingSitemapUpdateInput) {
            $this->assertPermission($admin, 'marketing.search_seo.sitemaps.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminMarketingSitemapCreateInput
            || ($data instanceof AdminMarketingSitemap && $operation instanceof Post)) {
            $this->assertPermission($admin, 'marketing.search_seo.sitemaps.create');

            return $this->handleCreate($this->resolveInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminMarketingSitemapUpdateInput
            || ($data instanceof AdminMarketingSitemap && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.search_seo.sitemaps.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.search_seo.sitemaps.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    protected function handleCreate(array $input): AdminMarketingSitemap
    {
        $this->validatePayload($input);

        Event::dispatch('marketing.search_seo.sitemap.create.before');

        $sitemap = $this->sitemapRepository->create([
            'file_name' => $input['file_name'],
            'path'      => $input['path'],
        ]);

        $sitemap = Sitemap::find($sitemap->id);

        Event::dispatch('marketing.search_seo.sitemap.create.after', $sitemap);

        return $this->itemProvider->mapToDtoPublic($sitemap);
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    protected function handleUpdate(int $id, array $input): AdminMarketingSitemap
    {
        $sitemap = Sitemap::find($id);
        if (! $sitemap) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.sitemap.not-found'));
        }

        $this->validatePayload($input);

        Event::dispatch('marketing.search_seo.sitemap.update.before', $id);

        $this->sitemapRepository->update([
            'file_name' => $input['file_name'],
            'path'      => $input['path'],
        ], $id);

        $sitemap = Sitemap::find($id);

        Event::dispatch('marketing.search_seo.sitemap.update.after', $sitemap);

        return $this->itemProvider->mapToDtoPublic($sitemap);
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    protected function handleDelete(int $id): array
    {
        $sitemap = Sitemap::find($id);
        if (! $sitemap) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.sitemap.not-found'));
        }

        // Remove any generated XML files from storage before deleting the row.
        try {
            $sitemap->deleteFromStorage();
        } catch (\Throwable $e) {
            report($e);
            // Continue — orphan files are not a hard failure.
        }

        Event::dispatch('marketing.search_seo.sitemap.delete.before', $id);

        try {
            $this->sitemapRepository->delete($id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.sitemap.delete-failed'),
                500,
            );
        }

        Event::dispatch('marketing.search_seo.sitemap.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.marketing.sitemap.deleted')];
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    protected function validatePayload(array $input): void
    {
        // Mirrors SitemapController validation rules verbatim.
        $rules = [
            'file_name' => ['required', 'regex:/^[\w\-\.]+$/', 'ends_with:.xml'],
            'path'      => ['required', 'starts_with:/', 'regex:/^(?!.*\/\/)[\w\-\.\/]+$/', 'ends_with:/'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    // ─── Permissions ─────────────────────────────────────────────────────────

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

    // ─── Input resolution ────────────────────────────────────────────────────

    protected function resolveInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && (
            $data instanceof AdminMarketingSitemapCreateInput
            || $data instanceof AdminMarketingSitemapUpdateInput
        )) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminMarketingSitemapUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    /** Map camelCase → snake_case and strip extraneous keys. */
    protected function normaliseArgs(array $input): array
    {
        $camelToSnake = [
            'fileName' => 'file_name',
        ];

        foreach ($camelToSnake as $camel => $snake) {
            if (array_key_exists($camel, $input) && ! array_key_exists($snake, $input)) {
                $input[$snake] = $input[$camel];
            }
            unset($input[$camel]);
        }

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
