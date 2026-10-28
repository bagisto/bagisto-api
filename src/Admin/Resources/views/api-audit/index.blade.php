<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.admin.api-audit.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('bagistoapi::app.admin.api-audit.index.title')
            </p>

            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('bagistoapi::app.admin.api-audit.index.info')
            </p>
        </div>

        @if (\Webkul\BagistoApi\Admin\Helper\AdminAuthHelper::hasPermission(auth()->guard('admin')->user(), 'settings.api_audit.delete'))
            <form
                ref="cleanupForm"
                method="POST"
                action="{{ route('admin.settings.api_audit.cleanup') }}"
                class="flex items-center gap-x-2.5"
            >
                @csrf

                <input
                    type="number"
                    name="days"
                    min="0"
                    value="90"
                    title="@lang('bagistoapi::app.admin.api-audit.index.cleanup-days')"
                    class="w-24 rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                />

                <button
                    type="button"
                    class="secondary-button"
                    @click="$emitter.emit('open-confirm-modal', {
                        message: @js(trans('bagistoapi::app.admin.api-audit.index.cleanup-confirm')),
                        agree: () => {
                            this.$refs['cleanupForm'].submit();
                        },
                    })"
                >
                    @lang('bagistoapi::app.admin.api-audit.index.cleanup-btn')
                </button>
            </form>
        @endif
    </div>

    {!! view_render_event('bagisto.admin.settings.api_audit.list.before') !!}

    <x-admin::datagrid :src="route('admin.settings.api_audit.index')" />

    {!! view_render_event('bagisto.admin.settings.api_audit.list.after') !!}
</x-admin::layouts>
