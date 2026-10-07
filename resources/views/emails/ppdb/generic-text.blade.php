{{ strtoupper($payload['headline'] ?? 'Informasi PPDB') }}
SMK Tahfizh Al-Fatih — Portal Penerimaan Siswa Baru
============================================================

@if($application)
Calon siswa       : {{ $application->name }}
Nomor pendaftaran : {{ $application->registration_number }}
@if($application->program)Program keahlian   : {{ $application->program->name }}
@endif
@if($application->period)Tahun ajaran       : {{ $application->period->academic_year }}
@endif

@endif
{!! trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ["\n", "\n", "\n", "\n\n"], $payload['body'] ?? 'Silakan buka Portal PPDB untuk melihat informasi selengkapnya.'))) !!}

@if(!empty($payload['cta_url']))
{{ $payload['cta'] ?? 'Buka Portal PPDB' }}:
{{ $payload['cta_url'] }}

@endif
KEAMANAN
Panitia tidak pernah meminta password atau kode admin melalui email, telepon, maupun WhatsApp.

Butuh bantuan? Hubungi kanal resmi sekolah atau buka {{ route('portal.dashboard') }}.

--
Email transaksional resmi SMK Tahfizh Al-Fatih.
