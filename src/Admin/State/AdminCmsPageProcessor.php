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
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCmsPage;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\SanitisesHtmlContent;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Core\Repositories\ChannelRepository;

class AdminCmsPageProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;
    use SanitisesHtmlContent;

    /** Slug regex used in place of \Webkul\Core\Rules\Slug (which is fatal in API context). */
    protected const SLUG_REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /**
     * Create a new instance.
     */
    public function __construct(
        protected PageRepository $pageRepository,
        protected ChannelRepository $channelRepository,
        protected AdminCmsPageItemProvider $itemProvider,
    ) {}

    /**
     * Route the CMS page request to create, update or delete.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if (
            $isGraphQL
            && $operation->getName() === 'delete'
            && $data instanceof AdminCmsPageUpdateInput
        ) {
            $this->assertPermission($admin, 'cms.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id, true);
        }

        if (
            $data instanceof AdminCmsPageCreateInput
            || (
                $data instanceof AdminCmsPage
                && $operation instanceof Post
            )
        ) {
            $this->assertPermission($admin, 'cms.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if (
            $data instanceof AdminCmsPageUpdateInput
            || (
                $data instanceof AdminCmsPage
                && $operation instanceof Put
            )
        ) {
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

    /**
     * Create a page from the payload, with its HTML content sanitised as core does.
     */
    protected function handleCreate(array $input, bool $isGraphQL = false): mixed
    {
        $this->validateCreatePayload($input);

        $input = $this->sanitiseHtmlFields($input, ['html_content']);

        Event::dispatch('cms.page.create.before');

        $page = $this->pageRepository->create([
            'page_title' => $input['page_title'] ?? null,
            'channels' => $input['channels'] ?? [],
            'html_content' => $input['html_content'] ?? null,
            'meta_title' => $input['meta_title'] ?? null,
            'url_key' => $input['url_key'] ?? null,
            'meta_keywords' => $input['meta_keywords'] ?? null,
            'meta_description' => $input['meta_description'] ?? null,
        ]);

        Event::dispatch('cms.page.create.after', $page);

        return $this->fetchAndMap((int) $page->id, $isGraphQL);
    }

    /**
     * Update the page's requested locale, with its HTML content sanitised as core does.
     */
    protected function handleUpdate(int $id, array $input, bool $isGraphQL = false): mixed
    {
        if (! $this->pageRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cms.page.not-found'));
        }

        $this->validateUpdatePayload($input, $id);

        $locale = $input['locale'] ?? app()->getLocale();

        $payload = [
            $locale => $this->sanitiseHtmlFields((array) ($input[$locale] ?? []), ['html_content']),
            'channels' => $input['channels'] ?? [],
            'locale' => $locale,
        ];

        request()->merge($payload);

        Event::dispatch('cms.page.update.before', $id);

        $page = $this->pageRepository->update($payload, $id);

        Event::dispatch('cms.page.update.after', $page);

        return $this->fetchAndMap($id, $isGraphQL);
    }

    /**
     * Delete the page, returning a snapshot of it when the caller asks for the resource.
     */
    protected function handleDelete(int $id, bool $asResource = false): ?AdminCmsPage
    {
        if (! $this->pageRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cms.page.not-found'));
        }

        $snapshot = $asResource
            ? AdminCmsPage::with(['translations', 'channels'])->find($id)
            : null;

        try {
            Event::dispatch('cms.page.delete.before', $id);

            $this->pageRepository->delete($id);

            Event::dispatch('cms.page.delete.after', $id);
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(
                __('bagistoapi::app.admin.cms.page.delete-failed'),
                500,
            );
        }

        if ($snapshot) {
            $snapshot->actionMessage = __('bagistoapi::app.admin.cms.page.deleted');
        }

        return $snapshot;
    }

    /**
     * Validate the create payload against core's page rules.
     */
    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'url_key' => ['required', 'string', 'regex:'.self::SLUG_REGEX],
            'page_title' => ['required', 'string'],
            'html_content' => ['required', 'string'],
            'channels' => ['required', 'array', 'min:1'],
        ];

        $value = Validator::make($input, $rules);

        if ($value->fails()) {
            throw new InvalidInputException($value->errors()->first(), 422);
        }

        $urlKey = $input['url_key'] ?? null;

        if (
            $urlKey
            && ! $this->pageRepository->isUrlKeyUnique(0, $urlKey)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cms.page.url-key-unique'), 422);
        }

        $this->validateChannels($input['channels'] ?? []);
    }

    /**
     * Validate the update payload for the requested locale against core's page rules.
     */
    protected function validateUpdatePayload(array $input, int $excludeId): void
    {
        $locale = $input['locale'] ?? app()->getLocale();

        $rules = [
            'channels' => ['required', 'array', 'min:1'],
        ];

        $rules[$locale.'.url_key'] = ['required', 'string', 'regex:'.self::SLUG_REGEX];
        $rules[$locale.'.page_title'] = ['required', 'string'];
        $rules[$locale.'.html_content'] = ['required', 'string'];

        $value = Validator::make($input, $rules);

        if ($value->fails()) {
            throw new InvalidInputException($value->errors()->first(), 422);
        }

        $urlKey = $input[$locale]['url_key'] ?? null;

        if (
            $urlKey
            && ! $this->pageRepository->isUrlKeyUnique($excludeId, $urlKey)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cms.page.url-key-unique'), 422);
        }

        $this->validateChannels($input['channels'] ?? []);
    }

    /**
     * Refuse an empty channel list or one naming an unknown channel.
     */
    protected function validateChannels(array $channels): void
    {
        if (empty($channels)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cms.page.channels-required'), 422);
        }

        $existing = $this->channelRepository
            ->findWhereIn('id', array_map('intval', $channels))
            ->pluck('id')
            ->map(fn ($value) => (int) $value)
            ->all();

        foreach ($channels as $channelId) {
            if (! in_array((int) $channelId, $existing, true)) {
                throw new InvalidInputException(__('bagistoapi::app.admin.cms.page.channels-invalid'), 422);
            }
        }
    }

    /**
     * Refuse an admin whose role lacks the given permission.
     */
    protected function assertPermission(object $admin, string $permission): void
    {
        if (! $this->adminHasPermission($admin, $permission)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.cms.page.no-permission'));
        }
    }

    /**
     * Read the create payload from the GraphQL arguments or the REST body.
     */
    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if (
            $isGraphQL
            && $data instanceof AdminCmsPageCreateInput
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($rawArgs);
        }

        return request()->all();
    }

    /**
     * Read the id of the page to update from the input or the GraphQL arguments.
     */
    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if (
            $data instanceof AdminCmsPageUpdateInput
            && $data->id
        ) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    /**
     * Read the update payload from the GraphQL arguments or the REST body.
     */
    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if (
            $isGraphQL
            && $data instanceof AdminCmsPageUpdateInput
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($rawArgs);
        }

        return request()->all();
    }

    /**
     * Map GraphQL camelCase arguments, top level and locale-keyed blocks, to the snake_case keys
     * the validator and the repository expect.
     */
    protected function dtoToArray(array $rawArgs): array
    {
        $result = [];

        $camelToSnake = [
            'urlKey' => 'url_key',
            'pageTitle' => 'page_title',
            'htmlContent' => 'html_content',
            'metaTitle' => 'meta_title',
            'metaKeywords' => 'meta_keywords',
            'metaDescription' => 'meta_description',
        ];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (
                is_array($value)
                && ! array_is_list($value)
            ) {
                $nested = [];

                foreach ($value as $field => $fieldValue) {
                    $snakeK = $camelToSnake[$field] ?? $field;
                    $nested[$snakeK] = $fieldValue;
                }

                $result[$key] = $nested;

                continue;
            }

            $snakeKey = $camelToSnake[$key] ?? $key;
            $result[$snakeKey] = $value;
        }

        return $result;
    }

    /**
     * Load the page afresh and shape it for the transport that asked.
     */
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
