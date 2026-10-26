@use('Webkul\BagistoApi\Admin\Helper\AdminAuthHelper')
@use('Webkul\BagistoApi\Repositories\CustomerApiSessionRepository')

@php
    $admin = auth()->guard('admin')->user();

    $canView = AdminAuthHelper::hasPermission($admin, 'customers.api_sessions');

    $canRevoke = AdminAuthHelper::hasPermission($admin, 'customers.api_sessions.delete');

    $customerId = (int) request()->route('id');

    $sessions = $canView
        ? app(CustomerApiSessionRepository::class)->activeForCustomer($customerId)
        : collect();
@endphp

@if ($canView)
    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <div class="flex items-center justify-between gap-2.5">
            <p class="text-base font-semibold text-gray-800 dark:text-white">
                @lang('bagistoapi::app.admin.customer-api-sessions.card.title', ['count' => $sessions->count()])
            </p>

            @if (
                $canRevoke
                && $sessions->isNotEmpty()
            )
                <form method="POST" action="{{ route('admin.customers.api_sessions.revoke_all', $customerId) }}">
                    @csrf

                    <button type="submit" class="secondary-button">
                        @lang('bagistoapi::app.admin.customer-api-sessions.datagrid.revoke-all')
                    </button>
                </form>
            @endif
        </div>

        @if ($sessions->isEmpty())
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                @lang('bagistoapi::app.admin.customer-api-sessions.card.empty')
            </p>
        @else
            <div class="mt-3 grid gap-2">
                @foreach ($sessions as $session)
                    <div class="flex flex-wrap justify-between gap-2 border-b pb-2 text-sm text-gray-600 last:border-b-0 dark:border-gray-800 dark:text-gray-300">
                        <span>{{ $session->name ?: trans('bagistoapi::app.admin.api-sessions.client-types.'.$session->client_type) }}</span>

                        <span>{{ $session->last_ip_address }}</span>

                        <span>{{ $session->last_used_at?->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
