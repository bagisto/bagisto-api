<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation as GraphQlMutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminConfigurationUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationUpdate;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Repositories\CoreConfigRepository;

/**
 * Bulk-updates configuration values for one slug, with server-side validation
 * sourced from the registered system_config tree (NEVER trust the client).
 *
 * See AdminConfigurationUpdate for the per-step pipeline.
 */
class AdminConfigurationUpdateProcessor implements ProcessorInterface
{
    /** Permission required for write. */
    protected const PERMISSION_KEY = 'configuration.edit';

    /** File-type fields the server treats as binary uploads. */
    protected const FILE_TYPES = ['image', 'file'];

    public function __construct(
        protected AdminConfigurationSchemaResolver $resolver,
        protected CoreConfigRepository $coreConfigRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // 1) Auth + permission
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.configuration.unauthenticated'));
        }
        $this->assertPermission($admin);

        $isGraphQL = $operation instanceof GraphQlMutation;

        // Pull the raw payload from the right place
        [$slug, $channel, $locale, $values] = $this->extractPayload($data, $context, $isGraphQL);

        // 2) Resolve slug
        if (! $slug) {
            throw new InvalidInputException(__('bagistoapi::app.admin.configuration.slug-required'), 422);
        }

        if (! $this->resolver->getItem($slug)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.configuration.slug-not-found'));
        }

        if (! is_array($values) || empty($values)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.configuration.values-required'), 422);
        }

        // 3) Anti-scope-escape + field-exists + custom-view rejection
        $fieldDefs = [];
        foreach ($values as $code => $_v) {
            if (! is_string($code) || ! str_starts_with($code.'.', $slug.'.')) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.configuration.scope-escape', ['key' => (string) $code]),
                    422,
                );
            }
            // Allow exact slug match? No — fields are always nested under the
            // slug. The condition above already requires `slug.` prefix.

            $field = $this->resolver->getField($code);
            if (! $field) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.configuration.unknown-field', ['key' => $code]),
                    422,
                );
            }
            if (! empty($field['path'])) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.configuration.custom-view-readonly', ['field' => $code]),
                    422,
                );
            }
            $fieldDefs[$code] = $field;
        }

        // 3.5) GraphQL: reject any file-type field with a non-string-path value
        if ($isGraphQL) {
            foreach ($fieldDefs as $code => $field) {
                if (in_array($field['type'] ?? null, self::FILE_TYPES, true)) {
                    $v = $values[$code] ?? null;
                    if (! is_string($v) || $v === '') {
                        throw new InvalidInputException(
                            __('bagistoapi::app.admin.configuration.file-upload-rest-only', ['field' => $code]),
                            422,
                        );
                    }
                }
            }
        }

        // 4) Validation — build rules from system_config validation strings
        $this->validateValues($values, $fieldDefs, $isGraphQL);

        // 5/6/7) Build the nested input shape the repository expects, then
        // call create() so its file-handling + per-field upsert runs unchanged.
        // The repository internally fires core.configuration.save.before/after.
        $nested = $this->buildNestedPayload($values, $fieldDefs);
        // The core repository requires `$data['locale']` and `$data['channel']`
        // to exist AND be truthy (its if-branch unsets them) OR be absent
        // entirely (loop skips them). Empty/null values pass the existence
        // check but crash the iteration. Always pass non-empty strings so the
        // unset-branch runs and they never reach the loop.
        $nested['channel'] = $channel ?: core()->getDefaultChannelCode();
        $nested['locale'] = $locale ?: app()->getLocale();

        // For multipart REST: alias request files at `values[<code>]` to the
        // leaf field name so the repository's `request()->hasFile($fieldName)`
        // lookup finds them (the repo keys by the leaf, not the dotted code).
        if (! $isGraphQL) {
            $this->aliasMultipartFiles($values, $fieldDefs);
        }

        $this->coreConfigRepository->create($nested);

        // 9) Re-resolve & return the freshly-effective values
        $effectiveChannel = $channel ?: core()->getRequestedChannelCode();
        $effectiveLocale = $locale ?: core()->getRequestedLocaleCode();
        $resolved = AdminConfigurationValuesProvider::buildPayload(
            $this->resolver,
            $slug,
            $effectiveChannel,
            $effectiveLocale,
        );

        $dto = new AdminConfigurationUpdate;
        $dto->slug = $slug;
        $dto->success = true;
        $dto->message = __('bagistoapi::app.admin.configuration.update-success');
        $dto->channel = $resolved['channel'];
        $dto->locale = $resolved['locale'];
        $dto->values = $resolved['values'];

        if ($isGraphQL) {
            return $dto;
        }

        // REST: wrap in JsonResponse to bypass IRI generation on the POPO DTO.
        return new JsonResponse([
            'success' => true,
            'message' => $dto->message,
            'slug'    => $dto->slug,
            'channel' => $dto->channel,
            'locale'  => $dto->locale,
            'values'  => $dto->values,
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Payload extraction
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Pull (slug, channel, locale, values) from REST request or GraphQL args.
     *
     * @return array{0:?string,1:?string,2:?string,3:array<string,mixed>}
     */
    protected function extractPayload(mixed $data, array $context, bool $isGraphQL): array
    {
        if ($isGraphQL) {
            $args = $context['args']['input'] ?? $context['args'] ?? [];
            $slug = $args['slug'] ?? null;
            $channel = $args['channel'] ?? null;
            $locale = $args['locale'] ?? null;
            $values = $args['values'] ?? [];

            if (
                empty($values)
                && $data instanceof AdminConfigurationUpdateInput
                && is_array($data->values)
            ) {
                $values = $data->values;
            }

            // GraphQL may pass values as JSON-encoded array under a single
            // "values" key when the schema treats it as Iterable
            if (is_string($values)) {
                $decoded = json_decode($values, true);
                $values = is_array($decoded) ? $decoded : [];
            }

            return [$slug, $channel, $locale, is_array($values) ? $values : []];
        }

        // REST — read from request() so multipart works
        $slug = request()->input('slug');
        $channel = request()->input('channel');
        $locale = request()->input('locale');
        $values = request()->input('values', []);

        if (! is_array($values)) {
            // Multipart can deliver `values` as a flat array of strings;
            // if it arrived as JSON in a single field, decode it.
            if (is_string($values)) {
                $decoded = json_decode($values, true);
                $values = is_array($decoded) ? $decoded : [];
            } else {
                $values = [];
            }
        }

        // Multipart files at `values[<code>]` show up in request()->allFiles()
        // — merge them into the values map so our normalisation sees them too.
        $files = request()->file('values') ?? [];
        if (is_array($files)) {
            foreach ($files as $code => $file) {
                if ($file instanceof UploadedFile) {
                    $values[$code] = $file;
                }
            }
        }

        return [$slug, $channel, $locale, $values];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Run a Laravel Validator against each (code => value) pair using the
     * field's registered `validation` string. Errors are returned in the
     * 422 message body as `field-errors: { code: [msg, ...] }`.
     */
    protected function validateValues(array $values, array $fieldDefs, bool $isGraphQL): void
    {
        $rules = [];
        $data = [];
        // Laravel's validator interprets dots in keys as nested access. Re-key
        // each (dotted) code to a safe alias for validation, mapping back when
        // surfacing errors.
        $aliasMap = [];
        $i = 0;

        foreach ($values as $code => $value) {
            $field = $fieldDefs[$code];
            $type = $field['type'] ?? 'text';
            $valString = $field['validation'] ?? null;

            // Files: the validation string usually contains mimes:..., but our
            // value is an UploadedFile object — let Laravel handle it. For
            // scalar JSON values that look like a path, skip validation.
            if (in_array($type, self::FILE_TYPES, true)) {
                if (is_string($value)) {
                    // Treating as a pre-uploaded path; skip the mimes rule
                    // (the file is no longer being uploaded).
                    continue;
                }
                if ($value instanceof UploadedFile && $valString) {
                    $alias = 'f_'.$i++;
                    $aliasMap[$alias] = $code;
                    $rules[$alias] = $valString;
                    $data[$alias] = $value;
                }

                continue;
            }

            if ($valString) {
                $alias = 'f_'.$i++;
                $aliasMap[$alias] = $code;
                $rules[$alias] = $valString;
                $data[$alias] = $value;
            }
        }

        if (empty($rules)) {
            return;
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            // Surface the underlying code in the message so the integrator
            // knows which field failed.
            $errors = $validator->errors()->toArray();
            $firstAlias = array_key_first($errors);
            $firstCode = $aliasMap[$firstAlias] ?? $firstAlias;
            $firstMsg = $errors[$firstAlias][0] ?? '';

            throw new InvalidInputException(
                __('bagistoapi::app.admin.configuration.validation-failed').' ['.$firstCode.'] '.$firstMsg,
                422,
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Repository payload shape
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Convert flat {dotted.code => value} into the nested array shape
     * CoreConfigRepository::create() expects, e.g.:
     *
     *   { sales: { order_settings: { reorder: { admin: '1' } } } }
     */
    protected function buildNestedPayload(array $values, array $fieldDefs): array
    {
        $out = [];
        foreach ($values as $code => $value) {
            // For uploaded files: persist them ourselves to the `configuration`
            // disk (mirrors the core repository's own file branch) and pass the
            // resulting path as the value. Doing this in the processor avoids
            // the repository's `request()->hasFile($dottedCode)` lookup, which
            // is unreliable because Laravel interprets dots as nested array
            // access and multipart `values[<dotted.code>]` arrives nested under
            // `values` rather than top-level.
            if ($value instanceof UploadedFile) {
                $persistValue = $value->store('configuration');
            } else {
                $persistValue = $value;
            }

            data_set($out, $code, $persistValue);
        }

        return $out;
    }

    /**
     * Multipart files arrive at request()->file('values')[<code>] but
     * CoreConfigRepository::create() looks them up via
     * request()->hasFile($fieldCode). Promote each file into the request's
     * top-level files map under its full dotted code so the lookup succeeds.
     */
    protected function aliasMultipartFiles(array $values, array $fieldDefs): void
    {
        $req = request();

        // Gather all current files into a flat array, add ours via data_set so
        // dotted codes become nested entries, then replace the FileBag in one
        // call. Laravel's `request()->hasFile($dotted)` interprets dots as
        // nested array access, so we must mirror that.
        $current = $req->allFiles();

        foreach ($values as $code => $value) {
            if ($value instanceof UploadedFile) {
                data_set($current, $code, $value);
            }
        }

        $req->files->replace($current);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Permission
    // ─────────────────────────────────────────────────────────────────────────

    protected function assertPermission(object $admin): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.configuration.no-permission'));
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

        if (in_array('*', $perms, true) || in_array(self::PERMISSION_KEY, $perms, true)) {
            return;
        }

        // Bagisto ACL exposes the menu under `configuration` (single key) —
        // accept either the fine-grained `.edit` or the parent key.
        if (in_array('configuration', $perms, true)) {
            return;
        }

        throw new AuthorizationException(__('bagistoapi::app.admin.configuration.no-permission'));
    }
}
