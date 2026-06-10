<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.integration.edit.title')
    </x-slot>

    @if ($token->isRevoked() || $token->isRegenerated())
        <div class="mb-4 rounded border border-yellow-300 bg-yellow-50 p-4 text-yellow-800 dark:border-yellow-700 dark:bg-yellow-900 dark:text-yellow-200">
            @lang('bagistoapi::app.integration.edit.history-banner')
            <strong>{{ trans('bagistoapi::app.integration.status.'.$token->status) }}</strong>
            @if ($token->isRegenerated() && $token->regenerated_to_id)
                — <a href="{{ route('admin.integration.edit', $token->regenerated_to_id) }}" class="underline">View successor</a>
            @endif
        </div>
    @endif

    <x-admin::form
        method="PUT"
        :action="route('admin.integration.update', $token->id)"
    >
        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('bagistoapi::app.integration.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.integration.token.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('bagistoapi::app.integration.edit.back-btn')
                </a>

                @unless ($token->isRevoked() || $token->isRegenerated())
                    <button type="submit" class="primary-button">
                        @lang('bagistoapi::app.integration.edit.save-btn')
                    </button>
                @endunless
            </div>
        </div>

        @include('bagistoapi::integration._form', [
            'token'           => $token,
            'availableAdmins' => $availableAdmins,
            'aclTree'         => $aclTree,
            'plainToken'      => $plainToken,
            'isEdit'          => true,
        ])
    </x-admin::form>
</x-admin::layouts>
