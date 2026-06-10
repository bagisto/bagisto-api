<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.integration.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('bagistoapi::app.integration.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('integration.create'))
                <a
                    href="{{ route('admin.integration.create') }}"
                    class="primary-button"
                >
                    @lang('bagistoapi::app.integration.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.integration.token.index')" />
</x-admin::layouts>
