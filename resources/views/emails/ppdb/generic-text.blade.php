{{ $payload['headline'] ?? 'Informasi PPDB' }} — SMK Tahfizh Al-Fatih
@if($application)
{{ $application->name }} — {{ $application->registration_number }}@if($application->period) — TA {{ $application->period->academic_year }}@endif
@endif
{!! strip_tags(str_replace(['<br>', '<br/>', '</p>'], ["\n", "\n", "\n"], $payload['body'] ?? '')) !!}
@if(!empty($payload['cta_url']))
{{ $payload['cta'] ?? 'Buka Portal' }}: {{ $payload['cta_url'] }}
@endif
