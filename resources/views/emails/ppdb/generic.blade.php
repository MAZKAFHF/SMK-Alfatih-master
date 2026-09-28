<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $payload['headline'] ?? 'SMK Tahfizh Al-Fatih' }}</title></head>
<body style="margin:0;padding:0;background:#f1f5f4;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#1e293b;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f4;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
<tr><td style="background:#063E32;padding:24px;text-align:center;">
<div style="display:inline-block;background:#ffffff;border-radius:10px;padding:6px 14px;font-weight:800;color:#063E32;letter-spacing:.08em;font-size:13px;">SMK TAHFIZH AL-FATIH</div>
<p style="margin:10px 0 0;color:#a7f3d0;font-size:12px;letter-spacing:.2em;">ALFATIH//FUTURE &bull; PPDB RESMI</p>
</td></tr>
<tr><td style="padding:28px 28px 8px;">
<h1 style="margin:0 0 8px;font-size:22px;color:#063E32;">{{ $payload['headline'] ?? 'Informasi PPDB' }}</h1>
@if(!empty($payload['preheader']))<p style="margin:0 0 12px;color:#64748b;font-size:13px;">{{ $payload['preheader'] }}</p>@endif
@if($application)
<p style="margin:0 0 12px;font-size:13px;color:#475569;">{{ $application->name }} &bull; <strong>{{ $application->registration_number }}</strong>@if($application->program) &bull; {{ $application->program->name }}@endif &bull; TA {{ $application->period?->academic_year ?? $application->academic_year }}</p>
@endif
<div style="font-size:15px;line-height:1.7;color:#334155;">{!! $payload['body'] ?? 'Silakan buka portal PPDB untuk detail.' !!}</div>
@if(!empty($payload['cta_url']))
<div style="margin:22px 0;text-align:center;">
<a href="{{ $payload['cta_url'] }}" style="display:inline-block;background:#087A55;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:13px 28px;border-radius:8px;">{{ $payload['cta'] ?? 'Buka Portal PPDB' }}</a>
</div>
@endif
</td></tr>
<tr><td style="padding:8px 28px 28px;font-size:12px;color:#64748b;line-height:1.6;">
<hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 14px;">
<p style="margin:0;">SMK Tahfizh Al-Fatih &bull; {{ config('app.name') }}<br>
@if(!empty($payload['whatsapp']))<a href="{{ $payload['whatsapp'] }}" style="color:#087A55;">Hubungi Admin PPDB via WhatsApp</a><br>@endif
Email balasan otomatis — hubungi kanal resmi sekolah untuk bantuan.</p>
</td></tr>
</table>
<p style="font-size:11px;color:#94a3b8;">&copy; {{ date('Y') }} SMK Tahfizh Al-Fatih. Pesan resmi PPDB.</p>
</td></tr>
</table>
</body>
</html>
