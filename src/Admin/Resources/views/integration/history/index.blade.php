<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.integration.history.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('bagistoapi::app.integration.history.index.title')
        </p>

        @if (bouncer()->hasPermission('integration.history.delete'))
            <form
                ref="cleanupForm"
                method="POST"
                action="{{ route('admin.integration.history.cleanup') }}"
                class="flex items-center gap-x-2.5"
            >
                @csrf

                <input
                    type="number"
                    name="days"
                    min="0"
                    value="90"
                    title="@lang('bagistoapi::app.integration.history.index.cleanup-days')"
                    class="w-24 rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                />

                <button
                    type="button"
                    class="secondary-button"
                    @click="$emitter.emit('open-confirm-modal', {
                        message: '@lang('bagistoapi::app.integration.history.index.cleanup-confirm')',
                        agree: () => {
                            this.$refs['cleanupForm'].submit();
                        },
                    })"
                >
                    @lang('bagistoapi::app.integration.history.index.cleanup-btn')
                </button>
            </form>
        @endif
    </div>

    <x-admin::datagrid :src="route('admin.integration.history.index')" />
</x-admin::layouts>
