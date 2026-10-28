@php
    $actor = auth()->guard('admin')->user();

    $canRename = \Webkul\BagistoApi\Admin\Helper\AdminAuthHelper::hasPermission($actor, 'settings.api_tokens.create');

    $canRevoke = ! $token->isRevoked()
        && \Webkul\BagistoApi\Admin\Helper\AdminAuthHelper::hasPermission($actor, 'settings.api_tokens.delete');

    $details = [
        'fields.admin' => $token->admin ? $token->admin->name.' ('.$token->admin->email.')' : null,
        'fields.domain' => $token->domain,
        'fields.token' => $token->token_preview ? '…'.$token->token_preview : null,
        'fields.status' => trans('bagistoapi::app.admin.api-tokens.status.'.$token->status()),
        'fields.expires-at' => $token->expires_at
            ? core()->formatDate($token->expires_at, 'Y-m-d H:i:s')
            : trans('bagistoapi::app.admin.api-tokens.datagrid.never'),
        'fields.created-by' => $token->createdBy?->name ?? trans('bagistoapi::app.admin.api-tokens.datagrid.console'),
        'fields.created-at' => $token->created_at ? core()->formatDate($token->created_at, 'Y-m-d H:i:s') : null,
        'fields.revoked-at' => $token->revoked_at ? core()->formatDate($token->revoked_at, 'Y-m-d H:i:s') : null,
        'fields.revoked-by' => $token->revokedBy?->name,
    ];

    $usage = [
        'fields.last-used-at' => $token->last_used_at ? core()->formatDate($token->last_used_at, 'Y-m-d H:i:s') : null,
        'fields.last-ip-address' => $token->last_ip_address,
        'fields.last-user-agent' => $token->last_user_agent,
        'fields.last-channel' => $token->last_channel ? trans('bagistoapi::app.admin.api-tokens.channels.'.$token->last_channel) : null,
        'fields.resolved-ips' => is_array($token->resolved_ips) && $token->resolved_ips ? implode(', ', $token->resolved_ips) : null,
        'fields.resolved-at' => $token->resolved_at ? core()->formatDate($token->resolved_at, 'Y-m-d H:i:s') : null,
        'fields.domain-mismatches' => (string) (int) $token->domain_mismatch_count,
        'fields.last-domain-mismatch-at' => $token->last_domain_mismatch_at ? core()->formatDate($token->last_domain_mismatch_at, 'Y-m-d H:i:s') : null,
    ];
@endphp

<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.admin.api-tokens.view.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ $token->name }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.settings.api_tokens.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('bagistoapi::app.admin.api-tokens.view.back-btn')
            </a>

            @if ($canRevoke)
                <v-api-token-revoke url="{{ route('admin.settings.api_tokens.delete', $token->id) }}">
                    <button
                        type="button"
                        class="secondary-button"
                    >
                        @lang('bagistoapi::app.admin.api-tokens.view.revoke-btn')
                    </button>
                </v-api-token-revoke>
            @endif
        </div>
    </div>

    @if ($plainText)
        <div class="mt-3.5 grid gap-2.5 rounded border border-green-400 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900">
            <p class="text-base font-semibold text-green-800 dark:text-green-200">
                @lang('bagistoapi::app.admin.api-tokens.view.token-once-title')
            </p>

            <p class="text-sm text-green-800 dark:text-green-200">
                @lang('bagistoapi::app.admin.api-tokens.view.token-once-info')
            </p>

            <v-api-token-plain-text token="{{ $plainText }}">
                <code class="break-all rounded bg-white p-2 text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-100">{{ $plainText }}</code>
            </v-api-token-plain-text>

            <p class="text-xs text-green-800 dark:text-green-200">
                @lang('bagistoapi::app.admin.api-tokens.view.usage-info')
            </p>
        </div>
    @endif

    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('bagistoapi::app.admin.api-tokens.view.details')
                </p>

                <div class="grid gap-3">
                    @foreach ($details as $key => $value)
                        <div class="grid grid-cols-[200px_1fr] gap-2 text-sm max-sm:grid-cols-1">
                            <p class="font-medium text-gray-600 dark:text-gray-300">
                                @lang('bagistoapi::app.admin.api-tokens.'.$key)
                            </p>

                            <p class="break-all text-gray-800 dark:text-white">
                                {{ $value ?? trans('bagistoapi::app.admin.api-tokens.view.not-yet') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('bagistoapi::app.admin.api-tokens.view.usage')
                </p>

                <div class="grid gap-3">
                    @foreach ($usage as $key => $value)
                        <div class="grid grid-cols-[200px_1fr] gap-2 text-sm max-sm:grid-cols-1">
                            <p class="font-medium text-gray-600 dark:text-gray-300">
                                @lang('bagistoapi::app.admin.api-tokens.'.$key)
                            </p>

                            <p class="break-all text-gray-800 dark:text-white">
                                {{ $value ?? trans('bagistoapi::app.admin.api-tokens.view.not-yet') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($canRename)
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <x-admin::form
                    method="PUT"
                    :action="route('admin.settings.api_tokens.update', $token->id)"
                >
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('bagistoapi::app.admin.api-tokens.fields.name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="name"
                                name="name"
                                rules="required|max:100"
                                :value="old('name', $token->name)"
                                :label="trans('bagistoapi::app.admin.api-tokens.fields.name')"
                            />

                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('bagistoapi::app.admin.api-tokens.view.save-btn')
                        </button>
                    </div>
                </x-admin::form>
            </div>
        @endif
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-api-token-plain-text-template"
        >
            <div class="flex items-center gap-2 max-sm:flex-wrap">
                <code class="flex-1 break-all rounded bg-white p-2 text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-100">@{{ token }}</code>

                <button
                    type="button"
                    class="secondary-button"
                    @click="copy"
                >
                    @lang('bagistoapi::app.admin.api-tokens.view.copy-btn')
                </button>
            </div>
        </script>

        <script type="module">
            app.component('v-api-token-plain-text', {
                template: '#v-api-token-plain-text-template',

                props: ['token'],

                methods: {
                    copy() {
                        navigator.clipboard.writeText(this.token).then(() => {
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: @json(trans('bagistoapi::app.admin.api-tokens.view.copied')),
                            });
                        });
                    },
                },
            });
        </script>

        <script
            type="text/x-template"
            id="v-api-token-revoke-template"
        >
            <div @click="revoke">
                <slot></slot>
            </div>
        </script>

        <script type="module">
            app.component('v-api-token-revoke', {
                template: '#v-api-token-revoke-template',

                props: ['url'],

                methods: {
                    revoke() {
                        this.$emitter.emit('open-confirm-modal', {
                            message: @json(trans('bagistoapi::app.admin.api-tokens.view.revoke-confirm')),

                            agree: () => {
                                this.$axios.delete(this.url)
                                    .then((response) => {
                                        this.$emitter.emit('add-flash', {
                                            type: 'success',
                                            message: response.data.message,
                                        });

                                        window.location.reload();
                                    })
                                    .catch((error) => {
                                        this.$emitter.emit('add-flash', {
                                            type: 'error',
                                            message: error.response?.data?.message,
                                        });
                                    });
                            },
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
