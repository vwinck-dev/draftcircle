<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $heading }}</title>
    </head>
    <body style="margin:0;padding:0;background:#eef3f9;color:#1e293b;font-family:Arial,sans-serif;">
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $body }}</div>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef3f9;">
            <tr>
                <td align="center" style="padding:32px 12px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;overflow:hidden;border-radius:22px;background:#ffffff;box-shadow:0 20px 50px rgba(15,23,42,.12);">
                        <tr>
                            <td style="padding:28px 34px;background:#0f172a;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="color:#ffffff;font-family:Georgia,serif;font-size:20px;font-weight:bold;">&#128214; {{ trans('ui.brand.name') }}</td>
                                        <td align="right" style="color:#93c5fd;font-size:11px;font-weight:bold;letter-spacing:1.6px;text-transform:uppercase;">{{ $eyebrow }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:36px 34px 12px;">
                                <p style="margin:0 0 12px;color:#64748b;font-size:14px;">{{ trans('ui.mail.greeting', ['name' => $recipientName]) }}</p>
                                <h1 style="margin:0;color:#0f172a;font-family:Georgia,serif;font-size:30px;line-height:1.18;">{{ $heading }}</h1>
                                <p style="margin:18px 0 0;color:#475569;font-size:16px;line-height:1.7;">{{ $body }}</p>
                            </td>
                        </tr>
                        @if (! empty($imageUrl) || ! empty($detailValue))
                            <tr>
                                <td style="padding:18px 34px 4px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #dbeafe;border-radius:16px;background:#eff6ff;">
                                        <tr>
                                            @if (! empty($imageUrl))
                                                <td width="112" style="padding:14px;">
                                                    <img src="{{ $imageUrl }}" alt="" width="84" height="112" style="display:block;width:84px;height:112px;border-radius:9px;object-fit:cover;box-shadow:0 8px 18px rgba(15,23,42,.16);">
                                                </td>
                                            @endif
                                            @if (! empty($detailValue))
                                                <td style="padding:18px 18px 18px {{ empty($imageUrl) ? '18px' : '4px' }};">
                                                    <div style="margin-bottom:7px;color:#2563eb;font-size:11px;font-weight:bold;letter-spacing:1.3px;text-transform:uppercase;">{{ $detailLabel }}</div>
                                                    <div style="color:#0f172a;font-family:Georgia,serif;font-size:19px;font-weight:bold;line-height:1.35;">{{ $detailValue }}</div>
                                                </td>
                                            @endif
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="padding:28px 34px 38px;">
                                <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 22px;border-radius:12px;color:#ffffff;background:#2563eb;box-shadow:0 10px 24px rgba(37,99,235,.25);font-size:14px;font-weight:bold;text-decoration:none;">{{ $actionText }} &rarr;</a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:22px 34px;border-top:1px solid #e2e8f0;color:#94a3b8;background:#f8fafc;font-size:12px;line-height:1.6;">
                                {{ trans('ui.mail.footer') }}<br>
                                {{ trans('ui.brand.motto') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
