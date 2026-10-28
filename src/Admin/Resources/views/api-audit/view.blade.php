@php
    $old = is_array($audit->old_values) ? $audit->old_values : [];

    $new = is_array($audit->new_values) ? $audit->new_values : [];

    $keys = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));

    /**
     * The printable form of a stored attribute value.
     */
    $fmt = function ($value) {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return (string) $value;
    };

    /**
     * The translated label of a token kind or channel, or a dash when the row has none.
     */
    $optionLabel = function (string $group, ?string $value) {
        if ($value === null) {
            return '—';
        }

        $key = 'bagistoapi::app.admin.api-audit.'.$group.'.'.str_replace('_', '-', $value);

        return trans()->has($key) ? trans($key) : $value;
    };

    /**
     * The translated label of an audit event.
     */
    $eventLabel = fn (?string $event) => in_array($event, ['created', 'updated', 'deleted'], true)
        ? trans('bagistoapi::app.admin.api-audit.events.'.$event)
        : (string) $event;

    $eventClass = [
        'created' => 'label-active',
        'updated' => 'label-pending',
        'deleted' => 'label-canceled',
    ][$audit->event] ?? 'label-info';

    $resourceLabel = ($audit->auditable_type ? class_basename($audit->auditable_type) : '—')
        .($audit->auditable_id ? ' #'.$audit->auditable_id : '');
@endphp

<x-admin::layouts>
    <x-slot:title>
        @lang('bagistoapi::app.admin.api-audit.view.title') #{{ $audit->id }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                @lang('bagistoapi::app.admin.api-audit.view.title') #{{ $audit->id }}
            </p>

            <span class="{{ $eventClass }} text-sm mx-1.5">
                {{ $eventLabel($audit->event) }}
            </span>
        </div>

        <a
            href="{{ route('admin.settings.api_audit.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            @lang('bagistoapi::app.admin.api-audit.view.back-btn')
        </a>
    </div>

    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white dark:bg-gray-900">
                <p class="p-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('bagistoapi::app.admin.api-audit.view.changes')
                </p>

                @if (count($keys))
                    <x-admin::table>
                        <x-admin::table.thead>
                            <x-admin::table.thead.tr>
                                <x-admin::table.th>
                                    @lang('bagistoapi::app.admin.api-audit.view.field')
                                </x-admin::table.th>

                                <x-admin::table.th>
                                    @lang('bagistoapi::app.admin.api-audit.view.old')
                                </x-admin::table.th>

                                <x-admin::table.th>
                                    @lang('bagistoapi::app.admin.api-audit.view.new')
                                </x-admin::table.th>
                            </x-admin::table.thead.tr>
                        </x-admin::table.thead>

                        <x-admin::table.tbody>
                            @foreach ($keys as $key)
                                <x-admin::table.tbody.tr class="border-b dark:border-gray-800">
                                    <x-admin::table.td class="font-medium">
                                        <span class="text-gray-800 dark:text-white">{{ $key }}</span>
                                    </x-admin::table.td>

                                    <x-admin::table.td>
                                        <span class="whitespace-pre-wrap break-all text-red-600 dark:text-red-400">{{ $fmt($old[$key] ?? null) }}</span>
                                    </x-admin::table.td>

                                    <x-admin::table.td>
                                        <span class="whitespace-pre-wrap break-all text-green-600 dark:text-green-400">{{ $fmt($new[$key] ?? null) }}</span>
                                    </x-admin::table.td>
                                </x-admin::table.tbody.tr>
                            @endforeach
                        </x-admin::table.tbody>
                    </x-admin::table>
                @else
                    <p class="px-4 pb-4 text-gray-600 dark:text-gray-300">
                        @lang('bagistoapi::app.admin.api-audit.view.no-field-changes')
                    </p>
                @endif
            </div>

            @if ($siblings->count())
                <div class="box-shadow rounded bg-white dark:bg-gray-900">
                    <p class="p-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('bagistoapi::app.admin.api-audit.view.same-request')
                    </p>

                    <x-admin::table>
                        <x-admin::table.thead>
                            <x-admin::table.thead.tr>
                                <x-admin::table.th>
                                    @lang('bagistoapi::app.admin.api-audit.view.action')
                                </x-admin::table.th>

                                <x-admin::table.th>
                                    @lang('bagistoapi::app.admin.api-audit.view.resource')
                                </x-admin::table.th>

                                <x-admin::table.th></x-admin::table.th>
                            </x-admin::table.thead.tr>
                        </x-admin::table.thead>

                        <x-admin::table.tbody>
                            @foreach ($siblings as $sibling)
                                <x-admin::table.tbody.tr class="border-b dark:border-gray-800">
                                    <x-admin::table.td>
                                        {{ $eventLabel($sibling->event) }}
                                    </x-admin::table.td>

                                    <x-admin::table.td>
                                        {{ $sibling->auditable_type ? class_basename($sibling->auditable_type) : '—' }}
                                        {{ $sibling->auditable_id ? '#'.$sibling->auditable_id : '' }}
                                    </x-admin::table.td>

                                    <x-admin::table.td>
                                        <a
                                            class="text-blue-600 hover:underline dark:text-blue-400"
                                            href="{{ route('admin.settings.api_audit.view', $sibling->id) }}"
                                        >
                                            @lang('bagistoapi::app.admin.api-audit.datagrid.view')
                                        </a>
                                    </x-admin::table.td>
                                </x-admin::table.tbody.tr>
                            @endforeach
                        </x-admin::table.tbody>
                    </x-admin::table>
                </div>
            @endif

            @if ($versions->count() > 1)
                <div class="box-shadow rounded bg-white dark:bg-gray-900">
                    <p class="p-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('bagistoapi::app.admin.api-audit.view.version-chain')
                    </p>

                    <x-admin::table>
                        <x-admin::table.thead>
                            <x-admin::table.thead.tr>
                                <x-admin::table.th>
                                    @lang('bagistoapi::app.admin.api-audit.view.version')
                                </x-admin::table.th>

                                <x-admin::table.th>
                                    @lang('bagistoapi::app.admin.api-audit.view.action')
                                </x-admin::table.th>

                                <x-admin::table.th>
                                    @lang('bagistoapi::app.admin.api-audit.view.date')
                                </x-admin::table.th>

                                <x-admin::table.th></x-admin::table.th>
                            </x-admin::table.thead.tr>
                        </x-admin::table.thead>

                        <x-admin::table.tbody>
                            @foreach ($versions as $version)
                                <x-admin::table.tbody.tr class="border-b dark:border-gray-800 @if ($version->id === $audit->id) bg-gray-50 dark:bg-gray-950 @endif">
                                    <x-admin::table.td class="font-medium">
                                        <span class="text-gray-800 dark:text-white">v{{ $version->version_id }}</span>
                                    </x-admin::table.td>

                                    <x-admin::table.td>
                                        {{ $eventLabel($version->event) }}
                                    </x-admin::table.td>

                                    <x-admin::table.td>
                                        {{ $version->created_at }}
                                    </x-admin::table.td>

                                    <x-admin::table.td>
                                        @if ($version->id !== $audit->id)
                                            <a
                                                class="text-blue-600 hover:underline dark:text-blue-400"
                                                href="{{ route('admin.settings.api_audit.view', $version->id) }}"
                                            >
                                                @lang('bagistoapi::app.admin.api-audit.datagrid.view')
                                            </a>
                                        @else
                                            <span class="text-gray-400">@lang('bagistoapi::app.admin.api-audit.view.title')</span>
                                        @endif
                                    </x-admin::table.td>
                                </x-admin::table.tbody.tr>
                            @endforeach
                        </x-admin::table.tbody>
                    </x-admin::table>
                </div>
            @endif
        </div>

        <div class="flex w-full max-w-sm flex-col gap-2 max-xl:max-w-full">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('bagistoapi::app.admin.api-audit.view.request-details')
                </p>

                <div class="grid gap-y-4">
                    <div class="grid gap-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('bagistoapi::app.admin.api-audit.view.resource')
                        </p>

                        <p class="break-all font-medium text-gray-800 dark:text-white">
                            {{ $resourceLabel }}
                        </p>
                    </div>

                    <div class="grid gap-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('bagistoapi::app.admin.api-audit.view.admin')
                        </p>

                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $audit->admin_name ?? '—' }}
                        </p>
                    </div>

                    <div class="grid gap-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('bagistoapi::app.admin.api-audit.view.token')
                        </p>

                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $audit->token_name ?? '—' }}
                            @if ($audit->token_id)
                                <span class="text-gray-500">(#{{ $audit->token_id }})</span>
                            @endif
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('bagistoapi::app.admin.api-audit.datagrid.token-kind')
                            </p>

                            <p class="font-medium text-gray-800 dark:text-white">
                                {{ $optionLabel('token-kinds', $audit->token_kind) }}
                            </p>
                        </div>

                        <div class="grid gap-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('bagistoapi::app.admin.api-audit.datagrid.channel')
                            </p>

                            <p class="font-medium text-gray-800 dark:text-white">
                                {{ $optionLabel('channels', $audit->channel) }}
                            </p>
                        </div>

                        <div class="grid gap-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('bagistoapi::app.admin.api-audit.view.version')
                            </p>

                            <p class="font-medium text-gray-800 dark:text-white">
                                v{{ $audit->version_id }}
                            </p>
                        </div>

                        <div class="grid gap-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('bagistoapi::app.admin.api-audit.view.method')
                            </p>

                            <p class="font-medium text-gray-800 dark:text-white">
                                {{ $audit->method ?? '—' }}
                            </p>
                        </div>

                        <div class="grid gap-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('bagistoapi::app.admin.api-audit.view.ip')
                            </p>

                            <p class="font-medium text-gray-800 dark:text-white">
                                {{ $audit->ip_address ?? '—' }}
                            </p>
                        </div>

                        <div class="grid gap-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('bagistoapi::app.admin.api-audit.view.date')
                            </p>

                            <p class="font-medium text-gray-800 dark:text-white">
                                {{ $audit->created_at }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('bagistoapi::app.admin.api-audit.view.url')
                        </p>

                        <p class="break-all font-medium text-gray-800 dark:text-white">
                            {{ $audit->url ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>
