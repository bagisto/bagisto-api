@component('admin::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            @lang('admin::app.emails.dear', ['admin_name' => $token->admin->name]), 👋
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            @lang('bagistoapi::integration.emails.'.$event.'.greeting', ['name' => $token->name])
        </p>
    </div>

    <!-- Event details -->
    <table style="width: 100%;border-collapse: collapse;margin-bottom: 32px;font-size: 14px;color: #384860;">
        <tr>
            <td style="padding: 8px 0;font-weight: 600;width: 40%;">@lang('bagistoapi::integration.emails.details.name')</td>
            <td style="padding: 8px 0;">{{ $token->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0;font-weight: 600;">@lang('bagistoapi::integration.emails.details.date')</td>
            <td style="padding: 8px 0;">{{ now()->format('d M Y, H:i') }}</td>
        </tr>
        @if ($ipAddress)
            <tr>
                <td style="padding: 8px 0;font-weight: 600;">@lang('bagistoapi::integration.emails.details.ip')</td>
                <td style="padding: 8px 0;">{{ $ipAddress }}</td>
            </tr>
        @endif
    </table>

    @if ($revokeUrl)
        <p style="font-size: 14px;color: #384860;line-height: 22px;margin-bottom: 16px;">
            @lang('bagistoapi::integration.emails.revoke-hint')
        </p>

        <a
            href="{{ $revokeUrl }}"
            style="display: inline-block;padding: 12px 28px;background: #DC2626;color: #ffffff;font-size: 14px;font-weight: 600;text-decoration: none;border-radius: 6px;margin-bottom: 32px;"
        >
            @lang('bagistoapi::integration.emails.revoke-btn')
        </a>

        <p style="font-size: 12px;color: #8E8E8E;line-height: 18px;">
            @lang('bagistoapi::integration.emails.revoke-expiry')
        </p>
    @else
        <p style="font-size: 14px;color: #384860;line-height: 22px;">
            @lang('bagistoapi::integration.emails.no-action')
        </p>
    @endif
@endcomponent
