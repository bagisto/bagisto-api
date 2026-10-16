<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@lang('bagistoapi::app.integration.revoke-confirmation.title')</title>
</head>
<body style="font-family: Arial, sans-serif;background: #F2F4F7;margin: 0;padding: 0;">
    <div style="max-width: 480px;margin: 80px auto;background: #ffffff;border-radius: 8px;padding: 40px;text-align: center;box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
        @if ($alreadyInactive)
            <h1 style="font-size: 20px;color: #121A26;margin-bottom: 16px;">
                @lang('bagistoapi::app.integration.revoke-confirmation.already-inactive-title')
            </h1>
            <p style="font-size: 14px;color: #384860;line-height: 22px;">
                @lang('bagistoapi::app.integration.revoke-confirmation.already-inactive-message', ['name' => $token->name])
            </p>
        @elseif ($confirmUrl)
            <h1 style="font-size: 20px;color: #121A26;margin-bottom: 16px;">
                @lang('bagistoapi::app.integration.revoke-confirmation.confirm-title')
            </h1>
            <p style="font-size: 14px;color: #384860;line-height: 22px;">
                @lang('bagistoapi::app.integration.revoke-confirmation.confirm-message', ['name' => $token->name])
            </p>
            <form method="POST" action="{{ $confirmUrl }}">
                <button type="submit" style="margin-top: 16px;padding: 10px 24px;background: #B91C1C;color: #ffffff;border: 0;border-radius: 6px;font-size: 14px;cursor: pointer;">
                    @lang('bagistoapi::app.integration.revoke-confirmation.confirm-button')
                </button>
            </form>
        @else
            <h1 style="font-size: 20px;color: #15803D;margin-bottom: 16px;">
                @lang('bagistoapi::app.integration.revoke-confirmation.success-title')
            </h1>
            <p style="font-size: 14px;color: #384860;line-height: 22px;">
                @lang('bagistoapi::app.integration.revoke-confirmation.success-message', ['name' => $token->name])
            </p>
        @endif
    </div>
</body>
</html>
