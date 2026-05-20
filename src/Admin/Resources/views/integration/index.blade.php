<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::integration.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('bagistoapi::integration.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('integration.create'))
                <a
                    href="{{ route('admin.integration.create') }}"
                    class="primary-button"
                >
                    @lang('bagistoapi::integration.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.integration.index')" />
</x-admin::layouts>
