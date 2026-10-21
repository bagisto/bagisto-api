<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.admin.api-tokens.create.title')
    </x-slot>

    <x-admin::form :action="route('admin.settings.api_tokens.store')">
        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('bagistoapi::app.admin.api-tokens.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.settings.api_tokens.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('bagistoapi::app.admin.api-tokens.create.back-btn')
                </a>

                @if ($admins->isNotEmpty())
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('bagistoapi::app.admin.api-tokens.create.save-btn')
                    </button>
                @endif
            </div>
        </div>

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('bagistoapi::app.admin.api-tokens.create.general')
                    </p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('bagistoapi::app.admin.api-tokens.fields.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="name"
                            name="name"
                            rules="required|max:100"
                            :value="old('name')"
                            :label="trans('bagistoapi::app.admin.api-tokens.fields.name')"
                            :placeholder="trans('bagistoapi::app.admin.api-tokens.fields.name')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('bagistoapi::app.admin.api-tokens.fields.admin')
                        </x-admin::form.control-group.label>

                        @if ($admins->isEmpty())
                            <p class="text-sm text-red-600">
                                @lang('bagistoapi::app.admin.api-tokens.create.no-admins')
                            </p>
                        @else
                            <x-admin::form.control-group.control
                                type="select"
                                id="admin_id"
                                name="admin_id"
                                rules="required"
                                :value="old('admin_id', auth()->guard('admin')->id())"
                                :label="trans('bagistoapi::app.admin.api-tokens.fields.admin')"
                            >
                                @foreach ($admins as $admin)
                                    <option value="{{ $admin->id }}">
                                        {{ $admin->name }} ({{ $admin->email }})
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('bagistoapi::app.admin.api-tokens.create.admin-info')
                            </p>
                        @endif

                        <x-admin::form.control-group.error control-name="admin_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('bagistoapi::app.admin.api-tokens.fields.domain')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="domain"
                            name="domain"
                            rules="required|max:2048"
                            :value="old('domain')"
                            :label="trans('bagistoapi::app.admin.api-tokens.fields.domain')"
                            placeholder="erp.example.com"
                        />

                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('bagistoapi::app.admin.api-tokens.create.domain-info')
                        </p>

                        <x-admin::form.control-group.error control-name="domain" />
                    </x-admin::form.control-group>
                </div>
            </div>

            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('bagistoapi::app.admin.api-tokens.create.expiry')
                    </p>

                    <v-api-token-expiry
                        mode="{{ old('expires_mode', 'date') }}"
                        :allow-no-expiry="{{ $allowNoExpiry ? 'true' : 'false' }}"
                    >
                    </v-api-token-expiry>

                    <x-admin::form.control-group.error control-name="expires_mode" />

                    <x-admin::form.control-group.error control-name="no_expiry_confirmed" />
                </div>
            </div>
        </div>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-api-token-expiry-template"
        >
            <div>
                <div class="mb-4 grid gap-2.5">
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-800 dark:text-white">
                        <input
                            type="radio"
                            name="expires_mode"
                            value="date"
                            v-model="currentMode"
                        />

                        @lang('bagistoapi::app.admin.api-tokens.create.expires-on-date')
                    </label>

                    <label
                        class="flex cursor-pointer items-center gap-2 text-sm text-gray-800 dark:text-white"
                        v-if="allowNoExpiry"
                    >
                        <input
                            type="radio"
                            name="expires_mode"
                            value="never"
                            v-model="currentMode"
                        />

                        @lang('bagistoapi::app.admin.api-tokens.create.never-expires')
                    </label>
                </div>

                <x-admin::form.control-group v-if="currentMode === 'date'">
                    <x-admin::form.control-group.label class="required">
                        @lang('bagistoapi::app.admin.api-tokens.fields.expires-at')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="date"
                        id="expires_at"
                        name="expires_at"
                        rules="required"
                        :value="old('expires_at')"
                        :label="trans('bagistoapi::app.admin.api-tokens.fields.expires-at')"
                    />

                    <x-admin::form.control-group.error control-name="expires_at" />
                </x-admin::form.control-group>

                <div
                    class="grid gap-2.5 rounded border border-yellow-300 bg-yellow-50 p-3 dark:border-yellow-700 dark:bg-yellow-900"
                    v-else
                >
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        @lang('bagistoapi::app.admin.api-tokens.create.no-expiry-warning')
                    </p>

                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        <input
                            type="checkbox"
                            name="no_expiry_confirmed"
                            value="1"
                        />

                        @lang('bagistoapi::app.admin.api-tokens.create.no-expiry-confirm')
                    </label>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-api-token-expiry', {
                template: '#v-api-token-expiry-template',

                props: {
                    mode: {
                        type: String,
                        default: 'date',
                    },

                    allowNoExpiry: {
                        type: Boolean,
                        default: true,
                    },
                },

                data() {
                    return {
                        currentMode: this.allowNoExpiry ? this.mode : 'date',
                    };
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
