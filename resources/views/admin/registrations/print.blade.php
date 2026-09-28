<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Berkas PPDB — {{ $registration->registration_number }}</title>
<style>@media print{ .no-print{display:none} thead{display:table-header-group} } body{font-family:Arial,sans-serif;color:#111;font-size:12px;margin:24px} .kop{text-align:center;border-bottom:3px double #111;padding-bottom:12px;margin-bottom:16px} table{width:100%;border-collapse:collapse;margin-top:8px} td,th{border:1px solid #999;padding:6px;text-align:left;vertical-align:top} h2{font-size:14px;margin:18px 0 4px}</style></head>
<body>
<div class="kop"><h1 style="margin:0">SMK TAHFIZH AL-FATIH</h1><p style="margin:2px">BERKAS PENDAFTARAN PPDB — {{ $registration->period?->academic_year }} &bull; Dicetak {{ now('Asia/Jakarta')->format('d M Y H:i') }} WIB</p></div>
<h2>Identitas</h2>
<table><tr><th width="30%">Nomor / Nama</th><td>{{ $registration->registration_number }} / {{ $registration->name }}</td></tr>
<tr><th>Program</th><td>{{ $registration->program?->name }}</td></tr>
<tr><th>TTL</th><td>{{ $registration->birth_place }}, {{ $registration->birth_date?->format('d-m-Y') }} ({{ $registration->gender }})</td></tr>
<tr><th>NIK / NISN</th><td>{{ $registration->nik }} / {{ $registration->nisn }}</td></tr>
<tr><th>Alamat</th><td>{{ $registration->address }}, {{ $registration->city }} {{ $registration->postal_code }}</td></tr>
<tr><th>Asal Sekolah</th><td>{{ $registration->school_origin }}</td></tr>
<tr><th>Orang Tua/Wali</th><td>Ayah: {{ $registration->father_name }} ({{ $registration->father_phone }})<br>Ibu: {{ $registration->mother_name }} ({{ $registration->mother_phone }})<br>Wali: {{ $registration->guardian_name }}</td></tr>
<tr><th>Status</th><td>{{ $registration->application_status->label() }}</td></tr></table>
<h2>Checklist Dokumen</h2>
<table><tr><th>Dokumen</th><th>Status</th></tr>@foreach($registration->documents as $d)<tr><td>{{ $d->type->label() }}</td><td>{{ $d->status->label() }}</td></tr>@endforeach</table>
<h2>Wawancara & Hasil</h2>
<table><tr><th>Jadwal</th><td>@if($registration->appointment){{ $registration->appointment->slot->date->format('d M Y') }} {{ $registration->appointment->slot->start_time }} — {{ $registration->appointment->slot->location }} ({{ $registration->appointment->status->label() }})@else — @endif</td></tr>
<tr><th>Hasil</th><td>@if($registration->decision?->released_at){{ $registration->decision->result->label() }}@else Menunggu @endif</td></tr></table>
<p class="no-print" style="margin-top:16px"><button onclick="window.print()">Cetak / Simpan PDF</button></p>
</body></html>
