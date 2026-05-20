<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.integration.create.title')
    </x-slot>

    <x-admin::form :action="route('admin.integration.store')">
        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('bagistoapi::app.integration.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.integration.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('bagistoapi::app.integration.create.back-btn')
                </a>

                <button type="submit" class="primary-button">
                    @lang('bagistoapi::app.integration.create.save-btn')
                </button>
            </div>
        </div>

        @include('bagistoapi::integration._form', [
            'token'           => null,
            'availableAdmins' => $availableAdmins,
            'aclTree'         => $aclTree,
            'plainToken'      => null,
            'isEdit'          => false,
        ])
    </x-admin::form>
</x-admin::layouts>
