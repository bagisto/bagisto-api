<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCmsPage;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CMS\Models\Page;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Core\Repositories\ChannelRepository;

/**
 * Handles POST, PUT, DELETE on the AdminCmsPage resource (Phase 2 — CRUD).
 *
 * Mirrors Webkul\Admin\Http\Controllers\CMS\PageController::
 *   store() / update() / delete()
 * — validation rules, slug regex (replacing the fatal core Slug rule), events.
 *
 * Permission resolution mirrors AdminCategoryProcessor: read role->permission_type
 * / role->permissions directly. Never call bouncer() (Sanctum-token requests have
 * no session-bound admin).
 */
class AdminCmsPageProcessor implements ProcessorInterface
{
    /** Slug regex used in place of \Webkul\Core\Rules\Slug (which is fatal in API context). */
    protected const SLUG_REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        protected PageRepository $pageRepository,
        protected ChannelRepository $channelRepository,
        protected AdminCmsPageItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminCmsPageUpdateInput) {
            $this->assertPermission($admin, 'cms.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminCmsPageCreateInput
            || ($data instanceof AdminCmsPage && $operation instanceof Post)) {
            $this->assertPermission($admin, 'cms.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($data instanceof AdminCmsPageUpdateInput
            || ($data instanceof AdminCmsPage && $operation instanceof Put)) {
            $this->assertPermission($admin, 'cms.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'cms.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input, bool $isGraphQL = false): mixed
    {
        $this->validateCreatePayload($input);

        Event::dispatch('cms.page.create.before');

        $page = $this->pageRepository->create([
            'page_title'       => $input['page_title'] ?? null,
            'channels'         => $input['channels'] ?? [],
            'html_content'     => $input['html_content'] ?? null,
            'meta_title'       => $input['meta_title'] ?? null,
            'url_key'          => $input['url_key'] ?? null,
            'meta_keywords'    => $input['meta_keywords'] ?? null,
            'meta_description' => $input['meta_description'] ?? null,
        ]);

        Event::dispatch('cms.page.create.after', $page);

        return $this->fetchAndMap((int) $page->id, $isGraphQL);
    }

    protected function handleUpdate(int $id, array $input, bool $isGraphQL = false): mixed
    {
        $page = Page::find($id);
        if (! $page) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cms.page.not-found'));
        }

        $this->validateUpdatePayload($input, $id);

        Event::dispatch('cms.page.update.before', $id);

        $locale = $input['locale'] ?? app()->getLocale();

        $payload = [
            $locale    => $input[$locale] ?? [],
            'channels' => $input['channels'] ?? [],
            'locale'   => $locale,
        ];

        $page = $this->pageRepository->update($payload, $id);

        Event::dispatch('cms.page.update.after', $page);

        return $this->fetchAndMap($id, $isGraphQL);
    }

    protected function handleDelete(int $id)
    {
        $page = Page::find($id);
        if (! $page) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cms.page.not-found'));
        }

        try {
            Event::dispatch('cms.page.delete.before', $id);

            $this->pageRepository->delete($id);

            Event::dispatch('cms.page.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.cms.page.delete-failed'),
                500,
            );
        }

        return null;
    }

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'url_key'      => ['required', 'string', 'regex:'.self::SLUG_REGEX],
            'page_title'   => ['required', 'string'],
            'html_content' => ['required', 'string'],
            'channels'     => ['required', 'array', 'min:1'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $urlKey = $input['url_key'] ?? null;
        if ($urlKey && $this->urlKeyTaken($urlKey, null)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cms.page.url-key-unique'), 422);
        }

        $this->validateChannels($input['channels'] ?? []);
    }

    protected function validateUpdatePayload(array $input, int $excludeId): void
    {
        $locale = $input['locale'] ?? app()->getLocale();

        $rules = [
            'channels' => ['required', 'array', 'min:1'],
        ];

        $rules[$locale.'.url_key'] = ['required', 'string', 'regex:'.self::SLUG_REGEX];
        $rules[$locale.'.page_title'] = ['required', 'string'];
        $rules[$locale.'.html_content'] = ['required', 'string'];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $urlKey = $input[$locale]['url_key'] ?? null;
        if ($urlKey && ! $this->pageRepository->isUrlKeyUnique($excludeId, $urlKey)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cms.page.url-key-unique'), 422);
        }

        $this->validateChannels($input['channels'] ?? []);
    }

    protected function urlKeyTaken(string $urlKey, ?int $excludePageId): bool
    {
        $q = \DB::table('cms_page_translations')->where('url_key', $urlKey);
        if ($excludePageId !== null) {
            $q->where('cms_page_id', '<>', $excludePageId);
        }

        return $q->limit(1)->exists();
    }

    protected function validateChannels(array $channels): void
    {
        if (empty($channels)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cms.page.channels-required'), 422);
        }

        $existing = \DB::table('channels')
            ->whereIn('id', array_map('intval', $channels))
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($channels as $cid) {
            if (! in_array((int) $cid, $existing, true)) {
                throw new InvalidInputException(__('bagistoapi::app.admin.cms.page.channels-invalid'), 422);
            }
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.cms.page.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.cms.page.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminCmsPageCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminCmsPageUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminCmsPageUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($rawArgs);
        }

        return request()->all();
    }

    /**
     * Map GraphQL camelCase top-level args to the snake_case form the validator + repo expect.
     * Locale-keyed blocks (e.g. 'en') are passed through as-is.
     */
    protected function dtoToArray(array $rawArgs): array
    {
        $result = [];

        $camelToSnake = [
            'urlKey'          => 'url_key',
            'pageTitle'       => 'page_title',
            'htmlContent'     => 'html_content',
            'metaTitle'       => 'meta_title',
            'metaKeywords'    => 'meta_keywords',
            'metaDescription' => 'meta_description',
        ];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value) && ! array_is_list($value)) {
                $nested = [];
                foreach ($value as $k => $v) {
                    $snakeK = $camelToSnake[$k] ?? $k;
                    $nested[$snakeK] = $v;
                }
                $result[$key] = $nested;

                continue;
            }

            $snakeKey = $camelToSnake[$key] ?? $key;
            $result[$snakeKey] = $value;
        }

        return $result;
    }

    protected function fetchAndMap(int $id, bool $isGraphQL = false): mixed
    {
        if ($isGraphQL) {
            return AdminCmsPage::with(['translations', 'channels'])->find($id);
        }

        $fresh = AdminCmsPage::with(['translations', 'channels'])->find($id);

        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $fresh);
    }
}
