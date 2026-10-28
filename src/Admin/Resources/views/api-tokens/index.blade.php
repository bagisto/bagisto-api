<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.admin.api-tokens.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('bagistoapi::app.admin.api-tokens.index.title')
            </p>

            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('bagistoapi::app.admin.api-tokens.index.info')
            </p>
        </div>

        @if (\Webkul\BagistoApi\Admin\Helper\AdminAuthHelper::hasPermission(auth()->guard('admin')->user(), 'settings.api_tokens.create'))
            <a
                href="{{ route('admin.settings.api_tokens.create') }}"
                class="primary-button"
            >
                @lang('bagistoapi::app.admin.api-tokens.index.create-btn')
            </a>
        @endif
    </div>

    {!! view_render_event('bagisto.admin.settings.api_tokens.list.before') !!}

    <x-admin::datagrid :src="route('admin.settings.api_tokens.index')" />

    {!! view_render_event('bagisto.admin.settings.api_tokens.list.after') !!}
</x-admin::layouts>
