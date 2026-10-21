<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.admin.api-sessions.index.title')
    </x-slot>

    <div class="grid gap-1.5">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('bagistoapi::app.admin.api-sessions.index.title')
        </p>

        <p class="text-sm text-gray-600 dark:text-gray-300">
            @lang('bagistoapi::app.admin.api-sessions.index.info')
        </p>
    </div>

    {!! view_render_event('bagisto.admin.settings.api_sessions.list.before') !!}

    <x-admin::datagrid :src="route('admin.settings.api_sessions.index')" />

    {!! view_render_event('bagisto.admin.settings.api_sessions.list.after') !!}
</x-admin::layouts>
