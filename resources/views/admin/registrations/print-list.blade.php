<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Daftar Pendaftar PPDB</title>
<style>
body{font-family:Arial,sans-serif;color:#111;font-size:11px;margin:20px}
.kop{text-align:center;border-bottom:3px double #111;padding-bottom:10px;margin-bottom:12px}
.kop h1{margin:0;font-size:18px}.kop p{margin:2px;font-size:11px}
table{width:100%;border-collapse:collapse;margin-top:8px}
th,td{border:1px solid #999;padding:5px;text-align:left;vertical-align:top;word-wrap:break-word}
thead{display:table-header-group}
tr{page-break-inside:avoid}
.no-print{display:none}
@media print{ .no-print{display:none} @page{size:A4 landscape;margin:12mm} }
</style></head>
<body>
<div class="kop"><h1>SMK TAHFIZH AL-FATIH</h1>
<p>DAFTAR PENDAFTAR PPDB @if($filters->isNotEmpty()) — {{ $filters->map(fn($v,$k) => $k.': '.$v)->join(' • ') }} @endif</p>
<p>Dicetak {{ now('Asia/Jakarta')->format('d M Y H:i') }} WIB • Total {{ $registrations->count() }} data</p></div>
<table><thead><tr><th>No</th><th>No. Pendaftaran</th><th>Nama</th><th>Program</th><th>Status Alur</th><th>Wawancara</th><th>Hasil</th><th>Tgl Daftar</th></tr></thead>
<tbody>
@forelse($registrations as $i => $r)
<tr><td>{{ $i+1 }}</td><td style="white-space:nowrap">{{ $r->registration_number }}</td><td>{{ $r->name }}</td><td>{{ $r->program?->name }}</td><td>{{ $r->application_status->label() }}</td><td>@if($r->appointment?->slot){{ $r->appointment->slot->date->format('d-m-Y') }} {{ $r->appointment->slot->start_time }}@else — @endif</td><td>{{ $r->decision?->result->label() ?? '—' }}</td><td>{{ $r->created_at->setTimezone('Asia/Jakarta')->format('d-m-Y H:i') }}</td></tr>
@empty
<tr><td colspan="8" style="text-align:center">Tidak ada data pada filter ini.</td></tr>
@endforelse
</tbody></table>
<p class="no-print" style="margin-top:14px"><button onclick="window.print()">Cetak / Simpan PDF</button></p>
</body></html>
