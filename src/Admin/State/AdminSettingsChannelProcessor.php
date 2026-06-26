<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsChannel;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\Channel;
use Webkul\Core\Repositories\ChannelRepository;

/**
 * Handles POST, PUT, DELETE on AdminSettingsChannel.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\ChannelController:
 *   store / update / destroy
 *
 * Permission resolution mirrors AdminCategoryProcessor — read
 * role->permission_type / role->permissions directly. No bouncer() calls.
 *
 * Delete guards (parity with monolith + the project's never-orphan rule):
 *   1. Refuse if this is the only remaining channel (HTTP 400).
 *   2. Refuse if its code matches config('app.channel') — the default app
 *      channel, used by every fallback in core (HTTP 400).
 */
class AdminSettingsChannelProcessor implements ProcessorInterface
{
    public function __construct(
        protected ChannelRepository $channelRepository,
        protected AdminSettingsChannelItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsChannelUpdateInput) {
            $this->assertPermission($admin, 'settings.channels.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminSettingsChannelCreateInput
            || ($data instanceof AdminSettingsChannel && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.channels.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($data instanceof AdminSettingsChannelUpdateInput
            || ($data instanceof AdminSettingsChannel && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.channels.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.channels.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input, bool $isGraphQL = false): AdminSettingsChannel|AdminSettingsChannelRestDto
    {
        $this->validateCreatePayload($input);

        $payload = $this->buildRepositoryPayload($input, isCreate: true);

        Event::dispatch('core.channel.create.before');
        $channel = $this->channelRepository->create($payload);
        Event::dispatch('core.channel.create.after', $channel);

        return $this->buildResult((int) $channel->id, $isGraphQL);
    }

    protected function handleUpdate(int $id, array $input, bool $isGraphQL = false): AdminSettingsChannel|AdminSettingsChannelRestDto
    {
        $channel = Channel::with(['locales', 'currencies', 'inventory_sources'])->find($id);
        if (! $channel) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.channel.not-found'));
        }

        $this->validateUpdatePayload($input, $id);

        $payload = $this->buildRepositoryPayload($input, isCreate: false);

        if (! array_key_exists('locales', $payload)) {
            $payload['locales'] = $channel->locales->pluck('id')->all();
        }
        if (! array_key_exists('currencies', $payload)) {
            $payload['currencies'] = $channel->currencies->pluck('id')->all();
        }
        if (! array_key_exists('inventory_sources', $payload)) {
            $payload['inventory_sources'] = $channel->inventory_sources->pluck('id')->all();
        }

        Event::dispatch('core.channel.update.before', $id);
        $updated = $this->channelRepository->update($payload, $id);
        Event::dispatch('core.channel.update.after', $updated);

        return $this->buildResult($id, $isGraphQL);
    }

    /**
     * Result of a create/update: the Eloquent model for GraphQL (connections +
     * homeSeo resolve), the flat RestDto for REST.
     */
    protected function buildResult(int $id, bool $isGraphQL): AdminSettingsChannel|AdminSettingsChannelRestDto
    {
        if ($isGraphQL) {
            return AdminSettingsChannel::with(['translations', 'locales', 'currencies', 'inventory_sources'])->find($id);
        }

        $fresh = Channel::with(['locales', 'currencies', 'inventory_sources', 'translations'])->find($id);

        return $this->itemProvider->buildRestDtoPublic($fresh);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminSettingsChannel
    {
        $channel = Channel::find($id);
        if (! $channel) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.channel.not-found'));
        }

        if (Channel::count() <= 1) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.channel.cannot-delete-last'),
                400,
            );
        }

        if ($channel->code === config('app.channel')) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.channel.cannot-delete-default'),
                400,
            );
        }

        $snapshotData = [
            'id'                => $id,
            'code'              => $channel->code,
            'hostname'          => $channel->hostname,
            'theme'             => $channel->theme,
            'timezone'          => $channel->timezone,
            'is_maintenance_on' => $channel->is_maintenance_on,
            'allowed_ips'       => $channel->allowed_ips,
            'home_seo'          => $channel->home_seo,
            'logo'              => $channel->logo,
            'favicon'           => $channel->favicon,
            'root_category_id'  => $channel->root_category_id,
            'default_locale_id' => $channel->default_locale_id,
            'base_currency_id'  => $channel->base_currency_id,
            'created_at'        => $channel->created_at,
            'updated_at'        => $channel->updated_at,
        ];

        try {
            Event::dispatch('core.channel.delete.before', $id);
            $this->channelRepository->delete($id);
            Event::dispatch('core.channel.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.channel.delete-failed'),
                500,
            );
        }

        if ($asResource) {
            $snapshot = (new AdminSettingsChannel)->forceFill($snapshotData);
            $snapshot->setRelation('translations', collect());
            $snapshot->setRelation('locales', collect());
            $snapshot->setRelation('currencies', collect());
            $snapshot->setRelation('inventory_sources', collect());
            $snapshot->actionMessage = __('bagistoapi::app.admin.settings.channel.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.settings.channel.deleted')];
    }

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'code'                => ['required', 'string', 'regex:/^[a-zA-Z]+[a-zA-Z0-9_-]*$/'],
            'name'                => ['required', 'string'],
            'hostname'            => ['nullable', 'string'],
            'locales'             => ['required', 'array', 'min:1'],
            'locales.*'           => ['integer'],
            'default_locale_id'   => ['required', 'integer'],
            'currencies'          => ['required', 'array', 'min:1'],
            'currencies.*'        => ['integer'],
            'base_currency_id'    => ['required', 'integer'],
            'inventory_sources'   => ['required', 'array', 'min:1'],
            'inventory_sources.*' => ['integer'],
            'root_category_id'    => ['required', 'integer'],
            'seo_title'           => ['nullable', 'string'],
            'seo_description'     => ['nullable', 'string'],
            'seo_keywords'        => ['nullable', 'string'],
            'is_maintenance_on'   => ['nullable', 'boolean'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        if (DB::table('channels')->where('code', $input['code'])->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.code-unique'), 422);
        }

        if (! empty($input['hostname'])
            && DB::table('channels')->where('hostname', $input['hostname'])->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.hostname-unique'), 422);
        }

        $this->assertCrossArrayMembership($input);
        $this->assertReferencedRowsExist($input);
    }

    protected function validateUpdatePayload(array $input, int $id): void
    {
        $rules = [
            'code'                => ['nullable', 'string', 'regex:/^[a-zA-Z]+[a-zA-Z0-9_-]*$/'],
            'hostname'            => ['nullable', 'string'],
            'locales'             => ['nullable', 'array', 'min:1'],
            'locales.*'           => ['integer'],
            'default_locale_id'   => ['nullable', 'integer'],
            'currencies'          => ['nullable', 'array', 'min:1'],
            'currencies.*'        => ['integer'],
            'base_currency_id'    => ['nullable', 'integer'],
            'inventory_sources'   => ['nullable', 'array', 'min:1'],
            'inventory_sources.*' => ['integer'],
            'root_category_id'    => ['nullable', 'integer'],
            'is_maintenance_on'   => ['nullable', 'boolean'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        if (! empty($input['code'])
            && DB::table('channels')->where('code', $input['code'])->where('id', '!=', $id)->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.code-unique'), 422);
        }

        if (! empty($input['hostname'])
            && DB::table('channels')->where('hostname', $input['hostname'])->where('id', '!=', $id)->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.hostname-unique'), 422);
        }

        $this->assertCrossArrayMembership($input);
        $this->assertReferencedRowsExist($input);
    }

    protected function assertCrossArrayMembership(array $input): void
    {
        if (isset($input['default_locale_id'], $input['locales'])
            && ! in_array((int) $input['default_locale_id'], array_map('intval', (array) $input['locales']), true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.default-locale-mismatch'), 422);
        }

        if (isset($input['base_currency_id'], $input['currencies'])
            && ! in_array((int) $input['base_currency_id'], array_map('intval', (array) $input['currencies']), true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.base-currency-mismatch'), 422);
        }
    }

    protected function assertReferencedRowsExist(array $input): void
    {
        if (! empty($input['locales'])) {
            $known = DB::table('locales')->whereIn('id', (array) $input['locales'])->count();
            if ($known !== count(array_unique((array) $input['locales']))) {
                throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.unknown-locale'), 422);
            }
        }

        if (! empty($input['currencies'])) {
            $known = DB::table('currencies')->whereIn('id', (array) $input['currencies'])->count();
            if ($known !== count(array_unique((array) $input['currencies']))) {
                throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.unknown-currency'), 422);
            }
        }

        if (! empty($input['inventory_sources'])) {
            $known = DB::table('inventory_sources')->whereIn('id', (array) $input['inventory_sources'])->count();
            if ($known !== count(array_unique((array) $input['inventory_sources']))) {
                throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.unknown-inventory-source'), 422);
            }
        }

        if (! empty($input['root_category_id'])
            && ! DB::table('categories')->where('id', (int) $input['root_category_id'])->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.channel.unknown-root-category'), 422);
        }
    }

    /**
     * Build the array `ChannelRepository::create/update` expects.
     *
     * Translatable broadcast: top-level scalars (name, description, seo_*,
     * maintenance_mode_text, home_page_content, footer_content) ride along at
     * the top level and the repository broadcasts them to every configured
     * locale. The optional `translations: { <locale>: { ... } }` map writes
     * per-locale overrides directly on top of the broadcast.
     *
     * SEO triplet (seo_title/seo_description/seo_keywords) is rolled into a
     * `home_seo` array shape (mirrors the monolith's setSEOContent helper).
     */
    protected function buildRepositoryPayload(array $input, bool $isCreate): array
    {
        $payload = [];

        $scalar = [
            'code', 'hostname', 'theme', 'timezone',
            'default_locale_id', 'base_currency_id',
            'root_category_id', 'is_maintenance_on', 'allowed_ips',
            'logo', 'favicon',
        ];
        foreach ($scalar as $k) {
            if (array_key_exists($k, $input) && $input[$k] !== null) {
                $payload[$k] = $input[$k];
            }
        }

        foreach (['locales', 'currencies', 'inventory_sources'] as $k) {
            if (! empty($input[$k])) {
                $payload[$k] = array_values((array) $input[$k]);
            }
        }

        foreach (['name', 'description', 'maintenance_mode_text', 'home_page_content', 'footer_content'] as $k) {
            if (array_key_exists($k, $input) && $input[$k] !== null) {
                $payload[$k] = $input[$k];
            }
        }

        $seoTop = [];
        foreach (['seo_title' => 'meta_title', 'seo_description' => 'meta_description', 'seo_keywords' => 'meta_keywords'] as $src => $dst) {
            if (! empty($input[$src])) {
                $seoTop[$dst] = $input[$src];
            }
        }
        if ($seoTop) {
            $payload['home_seo'] = $seoTop;
        }

        if (! empty($input['translations']) && is_array($input['translations'])) {
            foreach ($input['translations'] as $localeCode => $fields) {
                if (! is_string($localeCode) || ! is_array($fields)) {
                    continue;
                }
                $perLocale = [];
                foreach (['name', 'description', 'home_page_content', 'footer_content', 'maintenance_mode_text'] as $k) {
                    if (array_key_exists($k, $fields)) {
                        $perLocale[$k] = $fields[$k];
                    }
                }
                $perSeo = [];
                foreach (['seo_title' => 'meta_title', 'seo_description' => 'meta_description', 'seo_keywords' => 'meta_keywords'] as $src => $dst) {
                    if (! empty($fields[$src])) {
                        $perSeo[$dst] = $fields[$src];
                    }
                }
                if ($perSeo) {
                    $perLocale['home_seo'] = $perSeo;
                }
                if ($perLocale) {
                    $payload[$localeCode] = $perLocale;
                }
            }
        }

        return $payload;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.channel.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.channel.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsChannelCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsChannelUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsChannelUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map camelCase GraphQL args → snake_case the validator + repository expect.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'defaultLocaleId'      => 'default_locale_id',
            'baseCurrencyId'       => 'base_currency_id',
            'rootCategoryId'       => 'root_category_id',
            'inventorySources'     => 'inventory_sources',
            'seoTitle'             => 'seo_title',
            'seoDescription'       => 'seo_description',
            'seoKeywords'          => 'seo_keywords',
            'isMaintenanceOn'      => 'is_maintenance_on',
            'maintenanceModeText'  => 'maintenance_mode_text',
            'homePageContent'      => 'home_page_content',
            'footerContent'        => 'footer_content',
            'allowedIps'           => 'allowed_ips',
        ];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $snakeKey = $camelToSnake[$key] ?? $key;
            $result[$snakeKey] = $value;
        }

        foreach (get_object_vars($dto) as $key => $value) {
            if ($value !== null && ! array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
