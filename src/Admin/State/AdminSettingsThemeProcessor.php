<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTheme;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Theme\Models\ThemeCustomization;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

/**
 * Handles POST / PUT / DELETE for AdminSettingsTheme.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\ThemeController.
 *
 * Notes:
 *  - Image uploads inside `options` (image_carousel/services_content slides,
 *    static_content inline images) are deferred — only path strings are
 *    accepted in v1. The repository's uploadImage() flow expects UploadedFile
 *    instances behind a TinyMCE editor; reproducing it across REST + GraphQL
 *    is out of scope for v1. Use the admin panel UI for image uploads.
 *  - Permission resolution mirrors AdminSettingsLocaleProcessor — reads role
 *    permission_type/permissions directly, never calls bouncer().
 *  - Delete also wipes the theme's storage directory (theme/{id}) to match
 *    monolith parity.
 */
class AdminSettingsThemeProcessor implements ProcessorInterface
{
    public const ALLOWED_TYPES = [
        'product_carousel',
        'category_carousel',
        'static_content',
        'image_carousel',
        'footer_links',
        'services_content',
    ];

    public function __construct(
        protected AdminSettingsThemeItemProvider $itemProvider,
        protected ThemeCustomizationRepository $themeRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsThemeUpdateInput) {
            $this->assertPermission($admin, 'settings.themes.delete');
            $id = (int) basename((string) $this->resolveUpdateId($data, $context));

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminSettingsThemeCreateInput
            || ($data instanceof AdminSettingsTheme && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.themes.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminSettingsThemeUpdateInput
            || ($data instanceof AdminSettingsTheme && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.themes.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.themes.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminSettingsTheme
    {
        $payload = $this->normaliseCreatePayload($input);

        $this->validateCreatePayload($payload);

        Event::dispatch('theme_customization.create.before');

        $theme = ThemeCustomization::create([
            'name'       => $payload['name'],
            'sort_order' => $payload['sort_order'],
            'type'       => $payload['type'],
            'channel_id' => $payload['channel_id'],
            'theme_code' => $payload['theme_code'],
            'status'     => $payload['status'] ?? 0,
        ]);

        Event::dispatch('theme_customization.create.after', $theme);

        return $this->fetchAndMap((int) $theme->id);
    }

    protected function handleUpdate(int $id, array $input): AdminSettingsTheme
    {
        $existing = ThemeCustomization::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.theme.not-found'));
        }

        $payload = $this->normaliseUpdatePayload($input, $existing);

        $this->validateUpdatePayload($payload);

        Event::dispatch('theme_customization.update.before', $id);

        $locale = $payload['locale'];

        if (isset($payload[$locale]['options']) && is_array($payload[$locale]['options'])) {
            $payload[$locale]['options'] = $this->sanitiseOptions(
                $payload['type'],
                $payload[$locale]['options'],
            );
        }

        $repoPayload = [
            'name'       => $payload['name'],
            'sort_order' => $payload['sort_order'],
            'type'       => $payload['type'],
            'channel_id' => $payload['channel_id'],
            'theme_code' => $payload['theme_code'],
            'status'     => $payload['status'],
            'locale'     => $locale,
            $locale      => $payload[$locale] ?? ['options' => []],
        ];

        $existing->update([
            'name'       => $repoPayload['name'],
            'sort_order' => $repoPayload['sort_order'],
            'type'       => $repoPayload['type'],
            'channel_id' => $repoPayload['channel_id'],
            'theme_code' => $repoPayload['theme_code'],
            'status'     => $repoPayload['status'],
        ]);

        if (
            $repoPayload['type'] === 'static_content'
            && isset($repoPayload[$locale]['options']['html'])
        ) {
            $repoPayload[$locale]['options']['html'] = preg_replace(
                '/<script\b[^>]*>(.*?)<\/script>/is',
                '',
                (string) $repoPayload[$locale]['options']['html'],
            );
            if (isset($repoPayload[$locale]['options']['css'])) {
                $repoPayload[$locale]['options']['css'] = preg_replace(
                    '/<script\b[^>]*>(.*?)<\/script>/is',
                    '',
                    (string) $repoPayload[$locale]['options']['css'],
                );
            }
        }

        if (isset($repoPayload[$locale]['options'])) {
            $translation = $existing->translateOrNew($locale);
            $translation->options = $repoPayload[$locale]['options'];
            $translation->theme_customization_id = $existing->id;
            $translation->save();
        }

        Event::dispatch('theme_customization.update.after', $existing->fresh());

        return $this->fetchAndMap($id);
    }

    protected function handleDelete(int $id): array
    {
        $existing = ThemeCustomization::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.theme.not-found'));
        }

        try {
            Event::dispatch('theme_customization.delete.before', $id);

            $existing->delete();

            try {
                Storage::deleteDirectory('theme/'.$id);
            } catch (\Throwable $e) {
                report($e);
            }

            Event::dispatch('theme_customization.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.theme.delete-failed'),
                500,
            );
        }

        return ['message' => __('bagistoapi::app.admin.settings.theme.deleted')];
    }

    protected function validateCreatePayload(array $input): void
    {
        $channelIds = DB::table('channels')->pluck('id')->all();

        $rules = [
            'name'       => ['required', 'string'],
            'sort_order' => ['required', 'numeric'],
            'type'       => ['required', 'in:'.implode(',', self::ALLOWED_TYPES)],
            'channel_id' => ['required', 'integer', 'in:'.implode(',', $channelIds ?: [0])],
            'theme_code' => ['required', 'string'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function validateUpdatePayload(array $input): void
    {
        $channelIds = DB::table('channels')->pluck('id')->all();

        $rules = [
            'name'       => ['required', 'string'],
            'sort_order' => ['required', 'numeric'],
            'type'       => ['required', 'in:'.implode(',', self::ALLOWED_TYPES)],
            'channel_id' => ['required', 'integer', 'in:'.implode(',', $channelIds ?: [0])],
            'theme_code' => ['required', 'string'],
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.theme.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.theme.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsThemeCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsThemeUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsThemeUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map GraphQL camelCase args to snake_case for the validator.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'sortOrder' => 'sort_order',
            'channelId' => 'channel_id',
            'themeCode' => 'theme_code',
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

    protected function normaliseCreatePayload(array $input): array
    {
        return [
            'name'       => isset($input['name']) ? (string) $input['name'] : null,
            'sort_order' => isset($input['sort_order']) && $input['sort_order'] !== '' ? (int) $input['sort_order'] : null,
            'type'       => isset($input['type']) ? (string) $input['type'] : null,
            'channel_id' => isset($input['channel_id']) && $input['channel_id'] !== '' ? (int) $input['channel_id'] : null,
            'theme_code' => isset($input['theme_code']) ? (string) $input['theme_code'] : null,
            'status'     => $this->toBool($input['status'] ?? null),
        ];
    }

    protected function normaliseUpdatePayload(array $input, ThemeCustomization $existing): array
    {
        $locale = ! empty($input['locale']) ? (string) $input['locale'] : (core()?->getRequestedLocaleCode() ?? 'en');

        $out = [
            'name'       => isset($input['name']) && $input['name'] !== '' ? (string) $input['name'] : $existing->name,
            'sort_order' => isset($input['sort_order']) && $input['sort_order'] !== '' ? (int) $input['sort_order'] : (int) $existing->sort_order,
            'type'       => isset($input['type']) && $input['type'] !== '' ? (string) $input['type'] : $existing->type,
            'channel_id' => isset($input['channel_id']) && $input['channel_id'] !== '' ? (int) $input['channel_id'] : (int) $existing->channel_id,
            'theme_code' => isset($input['theme_code']) && $input['theme_code'] !== '' ? (string) $input['theme_code'] : $existing->theme_code,
            'status'     => array_key_exists('status', $input) ? $this->toBool($input['status']) : (bool) $existing->status,
            'locale'     => $locale,
        ];

        if (array_key_exists($locale, $input) && is_array($input[$locale])) {
            $out[$locale] = $input[$locale];
        } elseif (array_key_exists('options', $input) && is_array($input['options'])) {
            $out[$locale] = ['options' => $input['options']];
        }

        return $out;
    }

    protected function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 'on' || $value === '1' || $value === 1 || $value === 'true') {
            return true;
        }

        return false;
    }

    /**
     * Strip non-string image payloads from options. v1 accepts already-uploaded
     * path strings only.
     */
    protected function sanitiseOptions(string $type, array $options): array
    {
        if (in_array($type, ['image_carousel'], true)) {
            $images = [];
            foreach ($options['images'] ?? [] as $img) {
                if (is_array($img) && isset($img['image']) && is_string($img['image'])) {
                    $images[] = [
                        'image' => $img['image'],
                        'link'  => $img['link'] ?? null,
                        'title' => $img['title'] ?? null,
                    ];
                }
            }
            $options['images'] = $images;
        }

        if ($type === 'services_content') {
            $services = [];
            foreach ($options['services'] ?? [] as $svc) {
                if (is_array($svc) && isset($svc['service_icon']) && is_string($svc['service_icon'])) {
                    $services[] = [
                        'service_icon' => $svc['service_icon'],
                        'title'        => $svc['title'] ?? null,
                        'description'  => $svc['description'] ?? null,
                    ];
                }
            }
            $options['services'] = $services;
        }

        return $options;
    }

    protected function fetchAndMap(int $id): AdminSettingsTheme
    {
        $fresh = ThemeCustomization::with('translations')->find($id);

        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $fresh);
    }
}
