@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'fa', 'ur', 'he'], true);
    $dir = $isRtl ? 'rtl' : 'ltr';
    $align = $isRtl ? 'right' : 'left';
    $appName = config('app.name', 'Athr');
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $subject ?? $appName }}</title>
    <style>
        /* Clients that support embedded CSS get the polish; the rest fall back
           to the inline styles on every element below. */
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        table { border-collapse: collapse !important; }
        img { border: 0; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        a { color: #696CFF; }
        .athr-body-content p { margin: 0 0 16px 0; }
        .athr-body-content p:last-child { margin-bottom: 0; }
        .athr-body-content a { color: #696CFF; text-decoration: underline; }

        @media only screen and (max-width: 620px) {
            .athr-wrap { width: 100% !important; }
            .athr-pad { padding-left: 24px !important; padding-right: 24px !important; }
            .athr-title { font-size: 20px !important; line-height: 28px !important; }
        }

        @media (prefers-color-scheme: dark) {
            .athr-canvas { background-color: #25293c !important; }
            .athr-card { background-color: #2f3349 !important; }
            .athr-title, .athr-body-content { color: #e4e6f4 !important; }
            .athr-muted { color: #a1a5b7 !important; }
            .athr-divider { border-color: #444a67 !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; width:100%; background-color:#f5f5f9; -webkit-font-smoothing:antialiased;">

    {{-- Preheader: the grey preview line in the inbox, hidden in the body itself. --}}
    <div style="display:none; font-size:1px; color:#f5f5f9; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
        {{ \Illuminate\Support\Str::limit(strip_tags($body), 120) }}
    </div>

    <table class="athr-canvas" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f9;">
        <tr>
            <td align="center" style="padding:32px 12px;">

                <table class="athr-wrap" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px;">

                    <tr>
                        <td align="{{ $align }}" style="padding:0 8px 20px 8px; font-family:'Segoe UI',Tahoma,Arial,sans-serif; font-size:20px; font-weight:600; color:#696CFF; letter-spacing:0.2px;">
                            {{ $appName }}
                        </td>
                    </tr>

                    <tr>
                        <td class="athr-card" style="background-color:#ffffff; border-radius:12px; box-shadow:0 2px 8px rgba(34,48,62,0.06);">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">

                                <tr>
                                    <td style="height:4px; background-color:#696CFF; border-radius:12px 12px 0 0; font-size:0; line-height:0;">&nbsp;</td>
                                </tr>

                                <tr>
                                    <td class="athr-pad" align="{{ $align }}" style="padding:32px 40px 0 40px;">
                                        <h1 class="athr-title" style="margin:0; font-family:'Segoe UI',Tahoma,Arial,sans-serif; font-size:24px; line-height:32px; font-weight:600; color:#22303E;">
                                            {{ $subject ?? '' }}
                                        </h1>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="athr-pad" style="padding:20px 40px 0 40px;">
                                        <div class="athr-divider" style="border-top:1px solid #e8e8ee; font-size:0; line-height:0;">&nbsp;</div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="athr-pad athr-body-content" align="{{ $align }}" dir="{{ $dir }}" style="padding:24px 40px 36px 40px; font-family:'Segoe UI',Tahoma,Arial,sans-serif; font-size:16px; line-height:26px; color:#4b5563; text-align:{{ $align }}; word-break:break-word;">
                                        {!! nl2br(e($body)) !!}
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td class="athr-pad athr-muted" align="center" style="padding:24px 40px 0 40px; font-family:'Segoe UI',Tahoma,Arial,sans-serif; font-size:13px; line-height:20px; color:#8592A3;">
                            {{ __('communication::communication.email.footer_note') }}
                            <br>
                            &copy; {{ date('Y') }} {{ $appName }}
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
