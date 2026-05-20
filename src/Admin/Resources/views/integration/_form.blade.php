@php
    $permissionType = old('permission_type', $token?->permission_type ?? 'custom');
    $abilities      = old('permissions', $token?->abilities ?? []);
    $selectedAdmin  = old('admin_id', $token?->admin_id);
    $name           = old('name', $token?->name);
    $description    = old('description', $token?->description);

    $tokenIsActive   = $token && $token->isActive();
    $tokenIsDraft    = $token && $token->isDraft();
    $tokenIsHistoric = $token && ($token->isRevoked() || $token->isRegenerated());
    $card2Enabled    = ! $tokenIsHistoric;

    $expiresAt = $token?->expires_at;
    $rateMin   = $token?->rate_limit_per_minute;
    $rateDay   = $token?->rate_limit_per_day;

    $expiresMode = old('expires_mode', $expiresAt ? 'expires' : ($tokenIsActive ? 'expires' : 'never'));
    $rateMinMode = old('rate_min_mode', $rateMin !== null ? 'limited' : ($tokenIsActive ? 'limited' : 'unlimited'));
    $rateDayMode = old('rate_day_mode', $rateDay !== null ? 'limited' : ($tokenIsActive ? 'limited' : 'unlimited'));
@endphp

<div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
    {{-- Left: Access Control --}}
    <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('bagistoapi::integration.fields.access-control')
            </p>

            <v-integration-access-control>
                <div class="mb-4">
                    <div class="shimmer mb-1.5 h-4 w-24"></div>
                    <div class="custom-select h-11 w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all dark:border-gray-800 dark:bg-gray-900"></div>
                </div>

                <x-admin::shimmer.tree />
            </v-integration-access-control>
        </div>
    </div>

    {{-- Right: General + Card 2 --}}
    <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

        {{-- Card 1: General --}}
        <x-admin::accordion>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('bagistoapi::integration.fields.general')
                    </p>
                </div>
            </x-slot>

            <x-slot:content>
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('bagistoapi::integration.fields.name')
                    </x-admin::form.control-group.label>
                    <x-admin::form.control-group.control
                        type="text"
                        id="name"
                        name="name"
                        rules="required"
                        :value="$name"
                        :label="trans('bagistoapi::integration.fields.name')"
                        :placeholder="trans('bagistoapi::integration.fields.name')"
                        :disabled="$tokenIsHistoric"
                    />
                    <x-admin::form.control-group.error control-name="name" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('bagistoapi::integration.fields.description')
                    </x-admin::form.control-group.label>
                    <x-admin::form.control-group.control
                        type="textarea"
                        id="description"
                        name="description"
                        :value="$description"
                        :label="trans('bagistoapi::integration.fields.description')"
                        :placeholder="trans('bagistoapi::integration.fields.description')"
                        :disabled="$tokenIsHistoric"
                    />
                    <x-admin::form.control-group.error control-name="description" />
                </x-admin::form.control-group>

                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label class="required">
                        @lang('bagistoapi::integration.fields.assign-user')
                    </x-admin::form.control-group.label>

                    @if ($isEdit)
                        <input type="hidden" name="admin_id" value="{{ $token?->admin_id }}" />
                        <input
                            type="text"
                            disabled
                            value="{{ $token?->admin?->name }} ({{ $token?->admin?->email }})"
                            class="w-full rounded border border-gray-300 bg-gray-100 p-2 text-sm dark:border-gray-700 dark:bg-gray-800"
                        />
                    @else
                        @if ($availableAdmins->isEmpty())
                            <p class="text-sm text-red-600">
                                @lang('bagistoapi::integration.fields.no-available-admins')
                            </p>
                        @else
                            <x-admin::form.control-group.control
                                type="select"
                                name="admin_id"
                                id="admin_id"
                                rules="required"
                                :label="trans('bagistoapi::integration.fields.assign-user')"
                            >
                                <option value="">
                                    @lang('bagistoapi::integration.fields.select-admin')
                                </option>
                                @foreach ($availableAdmins as $availableAdmin)
                                    <option value="{{ $availableAdmin->id }}" @selected((int) $selectedAdmin === (int) $availableAdmin->id)>
                                        {{ $availableAdmin->name }} ({{ $availableAdmin->email }})
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>
                        @endif
                        <x-admin::form.control-group.error control-name="admin_id" />
                    @endif
                </x-admin::form.control-group>
            </x-slot>
        </x-admin::accordion>

        {{-- Card 2: only on Edit --}}
        @if ($isEdit)
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('bagistoapi::integration.fields.token-settings')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    @if ($plainToken)
                        <div class="mb-3 rounded border border-green-400 bg-green-50 p-3 dark:border-green-700 dark:bg-green-900">
                            <p class="mb-1 text-xs font-semibold text-green-800 dark:text-green-200">
                                ⚠️ @lang('bagistoapi::integration.edit.token-warning')
                            </p>
                            <div class="flex items-center gap-2">
                                <code class="flex-1 break-all rounded bg-white p-2 text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-100">{{ $plainToken }}</code>
                                <button
                                    type="button"
                                    class="secondary-button text-xs"
                                    onclick="navigator.clipboard.writeText('{{ $plainToken }}')"
                                >
                                    @lang('bagistoapi::integration.edit.copy-btn')
                                </button>
                            </div>
                        </div>
                    @elseif ($tokenIsActive)
                        <div class="mb-3 rounded border border-gray-300 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                @lang('bagistoapi::integration.edit.token-label'):
                                <code>{{ $token->id }}|{{ $token->token_preview }}...xxxx</code>
                            </p>
                            <p class="text-xs italic text-gray-500">@lang('bagistoapi::integration.edit.masked')</p>
                        </div>
                    @elseif ($tokenIsDraft)
                        <div class="mb-3 rounded border border-gray-300 bg-gray-50 p-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800">
                            @lang('bagistoapi::integration.edit.not-generated')
                        </div>
                    @endif

                    <div class="mb-4">
                        <p class="mb-1 text-sm font-medium">
                            @lang('bagistoapi::integration.fields.valid-till')
                        </p>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="expires_mode"
                                value="never"
                                @checked($expiresMode === 'never')
                                @disabled(! $card2Enabled)
                            />
                            <span>@lang('bagistoapi::integration.fields.never-expires')</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="expires_mode"
                                value="expires"
                                @checked($expiresMode === 'expires')
                                @disabled(! $card2Enabled)
                            />
                            <span>@lang('bagistoapi::integration.fields.expires-on'):</span>
                            <input
                                type="date"
                                name="expires_at"
                                value="{{ old('expires_at', $expiresAt?->format('Y-m-d')) }}"
                                @disabled(! $card2Enabled)
                                class="rounded border border-gray-300 p-1 text-sm dark:border-gray-700 dark:bg-gray-900"
                            />
                        </label>
                        <x-admin::form.control-group.error control-name="expires_at" />
                    </div>

                    <div class="mb-4">
                        <p class="mb-1 text-sm font-medium">
                            @lang('bagistoapi::integration.fields.rate-limit-per-minute')
                        </p>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="rate_min_mode"
                                value="unlimited"
                                @checked($rateMinMode === 'unlimited')
                                @disabled(! $card2Enabled)
                            />
                            <span>@lang('bagistoapi::integration.fields.unlimited')</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="rate_min_mode"
                                value="limited"
                                @checked($rateMinMode === 'limited')
                                @disabled(! $card2Enabled)
                            />
                            <span>@lang('bagistoapi::integration.fields.limit-to'):</span>
                            <input
                                type="number"
                                min="1"
                                name="rate_limit_per_minute"
                                value="{{ old('rate_limit_per_minute', $rateMin) }}"
                                @disabled(! $card2Enabled)
                                class="w-24 rounded border border-gray-300 p-1 text-sm dark:border-gray-700 dark:bg-gray-900"
                            />
                            <span class="text-xs text-gray-500">@lang('bagistoapi::integration.fields.requests-per-minute')</span>
                        </label>
                        <x-admin::form.control-group.error control-name="rate_limit_per_minute" />
                    </div>

                    <div class="mb-4">
                        <p class="mb-1 text-sm font-medium">
                            @lang('bagistoapi::integration.fields.rate-limit-per-day')
                        </p>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="rate_day_mode"
                                value="unlimited"
                                @checked($rateDayMode === 'unlimited')
                                @disabled(! $card2Enabled)
                            />
                            <span>@lang('bagistoapi::integration.fields.unlimited')</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="rate_day_mode"
                                value="limited"
                                @checked($rateDayMode === 'limited')
                                @disabled(! $card2Enabled)
                            />
                            <span>@lang('bagistoapi::integration.fields.limit-to'):</span>
                            <input
                                type="number"
                                min="1"
                                name="rate_limit_per_day"
                                value="{{ old('rate_limit_per_day', $rateDay) }}"
                                @disabled(! $card2Enabled)
                                class="w-28 rounded border border-gray-300 p-1 text-sm dark:border-gray-700 dark:bg-gray-900"
                            />
                            <span class="text-xs text-gray-500">@lang('bagistoapi::integration.fields.requests-per-day')</span>
                        </label>
                        <x-admin::form.control-group.error control-name="rate_limit_per_day" />
                    </div>

                    @unless ($tokenIsHistoric)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($tokenIsDraft)
                                @if (bouncer()->hasPermission('integration.generate'))
                                    <button
                                        type="button"
                                        class="primary-button"
                                        onclick="submitIntegrationGenerate();"
                                    >
                                        @lang('bagistoapi::integration.edit.generate-btn')
                                    </button>
                                @endif
                            @endif

                            @if ($tokenIsActive)
                                @if (bouncer()->hasPermission('integration.regenerate'))
                                    <button
                                        type="button"
                                        class="secondary-button"
                                        onclick="submitIntegrationRegenerate();"
                                    >
                                        @lang('bagistoapi::integration.edit.regenerate-btn')
                                    </button>
                                @endif

                                @if (bouncer()->hasPermission('integration.delete'))
                                    <button
                                        type="button"
                                        class="rounded-md px-3 py-1.5 text-sm font-bold text-red-600 hover:bg-gray-200 dark:text-red-400 dark:hover:bg-gray-800"
                                        onclick="submitIntegrationRevoke();"
                                    >
                                        @lang('bagistoapi::integration.edit.revoke-btn')
                                    </button>
                                @endif
                            @endif
                        </div>
                    @endunless
                </x-slot>
            </x-admin::accordion>
        @endif
    </div>
</div>

@if ($isEdit && ! $tokenIsHistoric)
    @push('scripts')
        <form id="generate-form" method="POST" action="{{ route('admin.integration.generate', $token->id) }}" class="hidden">@csrf</form>
        <form id="regenerate-form" method="POST" action="{{ route('admin.integration.regenerate', $token->id) }}" class="hidden">@csrf</form>
        <form id="revoke-form" method="POST" action="{{ route('admin.integration.destroy', $token->id) }}" class="hidden">@csrf @method('DELETE')</form>

        <script>
            (function () {
                // ── Generate / Regenerate submission with Card 2 value pass-through ─
                // Inputs are always editable. The server reads which radio is selected
                // (expires_mode/rate_*_mode) and uses the matching number/date value,
                // or stores NULL when the "Never expires" / "Unlimited" radio is picked.
                const card2FieldNames = [
                    'expires_mode',
                    'expires_at',
                    'rate_min_mode',
                    'rate_limit_per_minute',
                    'rate_day_mode',
                    'rate_limit_per_day',
                ];

                function readCard2Values () {
                    // Form fields live inside the surrounding main form. Grab them via
                    // the document-level field name so we don't have to know the form id.
                    const values = {};
                    card2FieldNames.forEach(function (name) {
                        const nodes = document.getElementsByName(name);
                        for (let i = 0; i < nodes.length; i++) {
                            const el = nodes[i];
                            if (el.form && el.form.id && el.form.id.startsWith('integration-action')) {
                                continue; // skip hidden duplicates inside the action forms
                            }
                            if (el.type === 'radio') {
                                if (el.checked) { values[name] = el.value; break; }
                            } else if (typeof el.value !== 'undefined') {
                                values[name] = el.value;
                                break;
                            }
                        }
                    });
                    return values;
                }

                function injectIntoForm (form, values) {
                    // Remove any previously-injected hidden inputs to avoid duplicates on repeat clicks.
                    form.querySelectorAll('[data-card2]').forEach(function (el) { el.remove(); });
                    Object.keys(values).forEach(function (key) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = values[key] ?? '';
                        input.dataset.card2 = '1';
                        form.appendChild(input);
                    });
                }

                function openConfirm (title, message, agree, agreeLabel) {
                    if (! window.emitter) {
                        // Fallback if for some reason the global emitter isn't available
                        if (confirm(message)) agree();
                        return;
                    }
                    window.emitter.emit('open-confirm-modal', {
                        title: title,
                        message: message,
                        options: {
                            btnDisagree: @json(trans('admin::app.components.modal.confirm.disagree-btn')),
                            btnAgree: agreeLabel || @json(trans('admin::app.components.modal.confirm.agree-btn')),
                        },
                        agree: agree,
                        disagree: function () {},
                    });
                }

                window.submitIntegrationGenerate = function () {
                    openConfirm(
                        @json(trans('bagistoapi::integration.confirm.generate.title')),
                        @json(trans('bagistoapi::integration.confirm.generate.message')),
                        function () {
                            const form = document.getElementById('generate-form');
                            injectIntoForm(form, readCard2Values());
                            form.submit();
                        }
                    );
                };

                window.submitIntegrationRegenerate = function () {
                    openConfirm(
                        @json(trans('bagistoapi::integration.confirm.regenerate.title')),
                        @json(trans('bagistoapi::integration.confirm.regenerate.message')),
                        function () {
                            document.getElementById('regenerate-form').submit();
                        }
                    );
                };

                window.submitIntegrationRevoke = function () {
                    openConfirm(
                        @json(trans('bagistoapi::integration.confirm.revoke.title')),
                        @json(trans('bagistoapi::integration.confirm.revoke.message')),
                        function () {
                            document.getElementById('revoke-form').submit();
                        }
                    );
                };
            })();
        </script>
    @endpush
@endif

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-integration-access-control-template"
    >
        <div>
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('bagistoapi::integration.fields.permission-type')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="permission_type"
                    id="permission_type"
                    rules="required"
                    v-model="permission_type"
                    :label="trans('bagistoapi::integration.fields.permission-type')"
                >
                    <option value="all">@lang('bagistoapi::integration.permission_type.all')</option>
                    <option value="custom">@lang('bagistoapi::integration.permission_type.custom')</option>
                    <option value="same_as_web">@lang('bagistoapi::integration.permission_type.same_as_web')</option>
                </x-admin::form.control-group.control>

                <x-admin::form.control-group.error control-name="permission_type" />
            </x-admin::form.control-group>

            <p v-if="permission_type === 'same_as_web'" class="mb-3 text-xs text-gray-500">
                @lang('bagistoapi::integration.fields.same-as-web-hint')
            </p>

            <div v-if="permission_type === 'custom'">
                <x-admin::form.control-group.error control-name="permissions" />

                <x-admin::tree.view
                    input-type="checkbox"
                    value-field="key"
                    id-field="key"
                    :items="json_encode(acl()->getItems())"
                    :value="json_encode($abilities)"
                    :fallback-locale="config('app.fallback_locale')"
                />
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-integration-access-control', {
            template: '#v-integration-access-control-template',

            data() {
                return {
                    permission_type: @json($permissionType),
                };
            },
        });
    </script>
@endPushOnce
