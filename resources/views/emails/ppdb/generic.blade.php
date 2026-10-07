@php
    $schoolName = \App\Models\SiteSetting::get('school_name', 'SMK Tahfizh Al-Fatih');
    $schoolEmail = \App\Models\SiteSetting::get('school_email', 'info@smkalfatih.sch.id');
    $schoolPhone = \App\Models\SiteSetting::get('school_phone');
    $logoPath = \App\Models\SiteSetting::get('logo');
    $logoUrl = $logoPath ? url('/storage/'.ltrim($logoPath, '/')) : null;
    $portalUrl = route('portal.dashboard');
    $headline = $payload['headline'] ?? 'Informasi PPDB';
    $preheader = $payload['preheader'] ?? $headline.' — '.$schoolName;
    $templateMeta = match ($template ?? '') {
        'verify_email' => ['label' => 'VERIFIKASI AKUN', 'accent' => '#0f8a63'],
        'application_submitted' => ['label' => 'PENDAFTARAN', 'accent' => '#0f8a63'],
        'interview_confirmed' => ['label' => 'JADWAL WAWANCARA', 'accent' => '#db7c16'],
        'document_revision' => ['label' => 'PERBAIKAN DOKUMEN', 'accent' => '#c2413b'],
        'decision_passed' => ['label' => 'HASIL SELEKSI', 'accent' => '#0f8a63'],
        'decision_not_passed' => ['label' => 'HASIL SELEKSI', 'accent' => '#64748b'],
        default => ['label' => 'INFORMASI RESMI PPDB', 'accent' => '#0f8a63'],
    };
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $headline }}</title>
    <style>
        @media only screen and (max-width: 640px) {
            .email-shell { padding: 16px 8px !important; }
            .email-card { border-radius: 18px !important; }
            .email-header, .email-body, .email-footer { padding-left: 22px !important; padding-right: 22px !important; }
            .email-title { font-size: 26px !important; line-height: 1.18 !important; }
            .email-button { display: block !important; text-align: center !important; }
            .application-grid td { display: block !important; width: 100% !important; padding-right: 0 !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#edf3f1;color:#17233c;font-family:Arial,'Helvetica Neue',sans-serif;-webkit-text-size-adjust:100%;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader }}&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#edf3f1;">
    <tr>
        <td class="email-shell" align="center" style="padding:34px 14px;">
            <table class="email-card" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:640px;background-color:#ffffff;border:1px solid #dbe7e3;border-radius:24px;overflow:hidden;box-shadow:0 16px 42px rgba(11,61,50,.10);">
                <tr><td style="height:6px;background:linear-gradient(90deg,#087a55 0%,#18a478 62%,#e89a2e 100%);font-size:0;line-height:0;">&nbsp;</td></tr>
                <tr>
                    <td class="email-header" style="padding:26px 34px 24px;background-color:#073f34;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                @if($logoUrl)
                                    <td width="66" valign="middle" style="width:66px;"><img src="{{ $logoUrl }}" width="54" height="54" alt="Logo {{ $schoolName }}" style="display:block;width:54px;height:54px;object-fit:contain;background:#ffffff;border-radius:13px;padding:4px;border:1px solid rgba(255,255,255,.55);"></td>
                                @endif
                                <td valign="middle">
                                    <p style="margin:0;color:#ffffff;font-size:17px;font-weight:800;line-height:1.25;letter-spacing:.01em;">{{ $schoolName }}</p>
                                    <p style="margin:5px 0 0;color:#a9d8c8;font-size:11px;font-weight:700;line-height:1.3;letter-spacing:.14em;">PORTAL PENERIMAAN SISWA BARU</p>
                                </td>
                                <td width="42" align="right" valign="middle" style="width:42px;"><div style="width:36px;height:36px;line-height:36px;text-align:center;border-radius:50%;background:#ffffff;color:#087a55;font-size:18px;font-weight:900;">✓</div></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="email-body" style="padding:36px 34px 12px;">
                        <p style="margin:0 0 13px;color:{{ $templateMeta['accent'] }};font-size:11px;font-weight:800;line-height:1.4;letter-spacing:.15em;">{{ $templateMeta['label'] }}</p>
                        <h1 class="email-title" style="margin:0 0 14px;color:#10233d;font-size:31px;font-weight:800;line-height:1.2;letter-spacing:-.025em;">{{ $headline }}</h1>
                        @if(!empty($payload['preheader']))<p style="margin:0 0 24px;color:#64748b;font-size:15px;line-height:1.65;">{{ $payload['preheader'] }}</p>@endif

                        @if($application)
                            <table class="application-grid" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;background:#f4f8f6;border:1px solid #dbe8e3;border-radius:14px;">
                                <tr>
                                    <td width="52%" style="padding:15px 10px 15px 18px;"><p style="margin:0 0 5px;color:#748397;font-size:10px;font-weight:800;letter-spacing:.11em;">CALON SISWA</p><p style="margin:0;color:#17233c;font-size:14px;font-weight:700;line-height:1.45;">{{ $application->name }}</p></td>
                                    <td width="48%" style="padding:15px 18px 15px 10px;"><p style="margin:0 0 5px;color:#748397;font-size:10px;font-weight:800;letter-spacing:.11em;">NOMOR PENDAFTARAN</p><p style="margin:0;color:#087a55;font-size:14px;font-weight:800;line-height:1.45;">{{ $application->registration_number }}</p></td>
                                </tr>
                                @if($application->program || $application->period)
                                    <tr><td colspan="2" style="padding:0 18px 15px;color:#64748b;font-size:12px;line-height:1.5;">@if($application->program){{ $application->program->name }}@endif @if($application->program && $application->period)&nbsp;&bull;&nbsp;@endif @if($application->period)Tahun Ajaran {{ $application->period->academic_year }}@endif</td></tr>
                                @endif
                            </table>
                        @endif

                        <div style="color:#334155;font-size:15px;line-height:1.75;">{!! $payload['body'] ?? '<p>Silakan buka Portal PPDB untuk melihat informasi selengkapnya.</p>' !!}</div>

                        @if(!empty($payload['cta_url']))
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:27px 0 22px;"><tr><td bgcolor="#087a55" style="border-radius:10px;box-shadow:0 7px 16px rgba(8,122,85,.20);"><a class="email-button" href="{{ $payload['cta_url'] }}" style="display:inline-block;padding:14px 25px;color:#ffffff;text-decoration:none;font-size:15px;font-weight:800;line-height:1.2;border-radius:10px;">{{ $payload['cta'] ?? 'Buka Portal PPDB' }} &nbsp;&rarr;</a></td></tr></table>
                            <p style="margin:0 0 24px;color:#7b8798;font-size:11px;line-height:1.6;word-break:break-all;">Jika tombol tidak berfungsi, salin tautan berikut ke browser:<br><a href="{{ $payload['cta_url'] }}" style="color:#087a55;text-decoration:underline;">{{ $payload['cta_url'] }}</a></p>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:4px 0 18px;background:#fff8e8;border-left:4px solid #e89a2e;border-radius:9px;"><tr><td style="padding:13px 15px;color:#705321;font-size:12px;line-height:1.6;"><strong>Jaga keamanan akun Anda.</strong> Panitia tidak pernah meminta password atau kode admin melalui email, telepon, maupun WhatsApp.</td></tr></table>
                    </td>
                </tr>
                <tr>
                    <td class="email-footer" style="padding:24px 34px 28px;background:#f7faf9;border-top:1px solid #e2ebe8;">
                        <p style="margin:0 0 7px;color:#314158;font-size:12px;font-weight:700;line-height:1.55;">Butuh bantuan dari panitia?</p>
                        <p style="margin:0;color:#718096;font-size:12px;line-height:1.7;"><a href="mailto:{{ $schoolEmail }}" style="color:#087a55;text-decoration:none;">{{ $schoolEmail }}</a>@if($schoolPhone)&nbsp;&bull;&nbsp; {{ $schoolPhone }}@endif<br><a href="{{ $portalUrl }}" style="color:#087a55;text-decoration:none;">Portal PPDB {{ $schoolName }}</a></p>
                    </td>
                </tr>
            </table>
            <p style="max-width:600px;margin:18px auto 0;color:#8a98a8;font-size:11px;line-height:1.6;text-align:center;">Email transaksional resmi {{ $schoolName }}.<br>&copy; {{ date('Y') }} {{ $schoolName }}. Mohon jangan membalas langsung ke email otomatis ini.</p>
        </td>
    </tr>
</table>
</body>
</html>
