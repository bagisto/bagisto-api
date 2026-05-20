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
