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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsLocaleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsLocaleUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsLocale;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\Locale;

/**
 * Handles POST / PUT / DELETE for the AdminSettingsLocale resource.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\LocaleController.
 *
 * Notes:
 *  - logo_path image upload is deferred — only a string path is accepted in v1.
 *    The Bagisto LocaleRepository expects logo_path as an UploadedFile[]; this
 *    processor writes via the Eloquent model directly to avoid that contract
 *    and fires the same before/after events the repository would have.
 *  - Permission resolution mirrors AdminSettingsExchangeRateProcessor — reads
 *    role->permission_type / role->permissions directly, never calls bouncer().
 *  - Delete refuses if it's the only remaining locale (400) or if any channel
 *    references it as default_locale_id (400).
 */
class AdminSettingsLocaleProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminSettingsLocaleItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsLocaleUpdateInput) {
            $this->assertPermission($admin, 'settings.locales.delete');
            $id = (int) basename((string) $this->resolveUpdateId($data, $context));

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminSettingsLocaleCreateInput
            || ($data instanceof AdminSettingsLocale && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.locales.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminSettingsLocaleUpdateInput
            || ($data instanceof AdminSettingsLocale && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.locales.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.locales.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminSettingsLocale
    {
        $payload = $this->normaliseCreatePayload($input);

        $this->validateCreatePayload($payload);

        Event::dispatch('core.locale.create.before');

        $locale = new Locale;
        $locale->code = $payload['code'];
        $locale->name = $payload['name'];
        $locale->direction = $payload['direction'];
        if (array_key_exists('logo_path', $payload)) {
            $locale->logo_path = $payload['logo_path'];
        }
        $locale->save();

        Event::dispatch('core.locale.create.after', $locale);

        return $this->fetchAndMap((int) $locale->id);
    }

    protected function handleUpdate(int $id, array $input): AdminSettingsLocale
    {
        $existing = Locale::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.locale.not-found'));
        }

        $payload = $this->normaliseUpdatePayload($input, $existing);

        $this->validateUpdatePayload($payload, $id);

        Event::dispatch('core.locale.update.before', $id);

        $existing->code = $payload['code'];
        $existing->name = $payload['name'];
        $existing->direction = $payload['direction'];
        if (array_key_exists('logo_path', $input)) {
            $existing->logo_path = is_string($input['logo_path']) || $input['logo_path'] === null
                ? $input['logo_path']
                : $existing->logo_path;
        }
        $existing->save();

        Event::dispatch('core.locale.update.after', $existing);

        return $this->fetchAndMap($id);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminSettingsLocale
    {
        $existing = Locale::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.locale.not-found'));
        }

        $this->assertDeletable($id);

        try {
            Event::dispatch('core.locale.delete.before', $id);

            $existing->delete();

            Event::dispatch('core.locale.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.locale.delete-failed'),
                500,
            );
        }

        if ($asResource) {
            $snapshot = $this->mapModel($existing);
            $snapshot->message = __('bagistoapi::app.admin.settings.locale.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.settings.locale.deleted')];
    }

    protected function assertDeletable(int $id): void
    {
        if (Locale::count() <= 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.locale.cannot-delete-last'), 400);
        }

        if (DB::table('channels')->where('default_locale_id', $id)->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.locale.cannot-delete-channel-default'), 400);
        }
    }

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'code'      => ['required', 'string', 'unique:locales,code', 'regex:/^[a-z0-9_-]+$/'],
            'name'      => ['required', 'string'],
            'direction' => ['required', 'in:ltr,rtl'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function validateUpdatePayload(array $input, int $excludeId): void
    {
        $rules = [
            'code'      => ['required', 'string', 'regex:/^[a-z0-9_-]+$/', 'unique:locales,code,'.$excludeId],
            'name'      => ['required', 'string'],
            'direction' => ['required', 'in:ltr,rtl'],
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.locale.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.locale.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsLocaleCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsLocaleUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsLocaleUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map GraphQL camelCase args to snake_case the validator expects.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'logoPath' => 'logo_path',
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

    /**
     * Strip non-scalar logo_path (the monolith expects an UploadedFile[] but
     * v1 of this API accepts string only — anything else is ignored).
     */
    protected function normaliseCreatePayload(array $input): array
    {
        $out = [
            'code'      => isset($input['code']) ? (string) $input['code'] : null,
            'name'      => isset($input['name']) ? (string) $input['name'] : null,
            'direction' => isset($input['direction']) ? (string) $input['direction'] : null,
        ];

        if (array_key_exists('logo_path', $input) && (is_string($input['logo_path']) || $input['logo_path'] === null)) {
            $out['logo_path'] = $input['logo_path'];
        }

        return $out;
    }

    protected function normaliseUpdatePayload(array $input, Locale $existing): array
    {
        return [
            'code'      => isset($input['code']) && $input['code'] !== '' && $input['code'] !== null
                ? (string) $input['code']
                : $existing->code,
            'name'      => isset($input['name']) && $input['name'] !== '' && $input['name'] !== null
                ? (string) $input['name']
                : $existing->name,
            'direction' => isset($input['direction']) && $input['direction'] !== '' && $input['direction'] !== null
                ? (string) $input['direction']
                : $existing->direction,
        ];
    }

    protected function fetchAndMap(int $id): AdminSettingsLocale
    {
        return $this->mapModel(Locale::find($id));
    }

    protected function mapModel(object $model): AdminSettingsLocale
    {
        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $model);
    }
}
