<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PPDBRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ppdb_registrations';

    protected $fillable = [
        'registration_number',
        'applicant_account_id',
        'period_id',
        'application_status',
        'source',
        'created_by',
        'name',
        'nik',
        'nisn',
        'birth_place',
        'birth_date',
        'gender',
        'address',
        'province',
        'city',
        'district',
        'village',
        'postal_code',
        'school_origin',
        'school_npsn',
        'graduation_year',
        'phone',
        'email',
        'parent_name',
        'father_name',
        'father_phone',
        'father_occupation',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'photo_path',
        'program_id',
        'status',
        'admin_notes',
        'academic_year',
        'sequence',
        'submitted_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'status' => RegistrationStatus::class,
            'application_status' => ApplicationStatus::class,
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PPDBRegistration $registration): void {
            if (is_null($registration->registration_number)) {
                $registration->registration_number = static::generateRegistrationNumberAtomic($registration->period_id);
            }
            if (empty($registration->academic_year)) {
                $period = $registration->period_id ? PpdbPeriod::find($registration->period_id) : PpdbPeriod::active();
                $registration->academic_year = $period?->academic_year ?? now('Asia/Jakarta')->format('Y').'/'.now('Asia/Jakarta')->addYear()->format('Y');
            }
            if (empty($registration->period_id)) {
                $registration->period_id = PpdbPeriod::active()?->id;
            }
            if (empty($registration->application_status)) {
                $registration->application_status = ApplicationStatus::Draft;
            }
            // Kolom status denormalisasi dipertahankan untuk kompatibilitas skema;
            // application_status tetap satu-satunya sumber alur produk.
            $registration->status = $registration->application_status instanceof ApplicationStatus
                ? $registration->application_status->toLegacy()
                : ApplicationStatus::fromLegacy((string) ($registration->status ?? 'pending'))->toLegacy();
            // Normalize phone/email
            if ($registration->phone) {
                $registration->phone = trim((string) $registration->phone);
            }
            if ($registration->email) {
                $registration->email = strtolower(trim((string) $registration->email));
            }
            $registration->name = trim((string) $registration->name);
        });
    }

    /**
     * Concurrency-safe generation using a transaction-scoped PostgreSQL
     * advisory lock (production) plus a locked latest row on other drivers.
     * PostgreSQL does not allow FOR UPDATE directly on aggregate queries.
     */
    public static function generateRegistrationNumberAtomic(?int $periodId = null): string
    {
        return DB::transaction(function () use ($periodId) {
            $year = now()->year;

            if (DB::connection()->getDriverName() === 'pgsql') {
                // One lock namespace per registration year. It is released
                // automatically when the surrounding transaction completes.
                DB::select('SELECT pg_advisory_xact_lock(70821, ?)', [$year]);
            }

            $max = static::withTrashed()
                ->where('registration_number', 'like', "PPDB-{$year}-%")
                ->orderByDesc('registration_number')
                ->lockForUpdate()
                ->value('registration_number');

            $next = 1;
            if ($max) {
                $num = (int) substr((string) $max, -5);
                $next = $num + 1;
            } else {
                $globalMax = static::withTrashed()->max('id') ?? 0;
                $next = $globalMax + 1;
                $existingYearMax = static::whereYear('created_at', $year)->count() + 1;
                $next = max($next, $existingYearMax);
            }

            // Hindari tabrakan dengan periode lain: pastikan sequence per-periode naik
            if ($periodId) {
                $periodMax = static::withTrashed()->where('period_id', $periodId)->max('sequence') ?? 0;
                $next = max($next, $periodMax + 1);
            }

            $candidate = sprintf('PPDB-%s-%05d', $year, $next);
            $attempt = 0;
            while (static::withTrashed()->where('registration_number', $candidate)->exists() && $attempt < 5) {
                $next++;
                $candidate = sprintf('PPDB-%s-%05d', $year, $next);
                $attempt++;
            }

            return $candidate;
        }, 3);
    }

    public static function generateRegistrationNumber(?int $periodId = null): string
    {
        return static::generateRegistrationNumberAtomic($periodId);
    }

    /**
     * Override create to handle unique race for parallel inserts.
     */
    public static function create(array $attributes = [])
    {
        $attempts = 0;
        while (true) {
            try {
                return static::query()->create($attributes);
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = $e->getMessage();
                if (($attempts < 5) && (str_contains($msg, 'UNIQUE') || str_contains($msg, 'unique') || str_contains($msg, 'database is locked') || str_contains($msg, 'busy'))) {
                    $attempts++;
                    usleep(100000 * $attempts + random_int(0, 50000));
                    // force regeneration of number on next try
                    $attributes['registration_number'] = null;
                    // also need to handle that booted will generate anew, but if attributes has null, it will generate
                    continue;
                }
                throw $e;
            }
        }
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PpdbPeriod::class, 'period_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_account_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(InterviewAppointment::class, 'application_id');
    }

    public function decision(): HasOne
    {
        return $this->hasOne(ApplicationDecision::class, 'application_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(InternalNote::class, 'application_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'application_id')->orderByDesc('id');
    }

    public function isLockedForApplicant(): bool
    {
        return in_array($this->application_status, [
            ApplicationStatus::Submitted, ApplicationStatus::Resubmitted,
            ApplicationStatus::Verified, ApplicationStatus::WaitingSlot,
            ApplicationStatus::Scheduled, ApplicationStatus::Interviewed,
            ApplicationStatus::WaitingDecision, ApplicationStatus::Passed,
            ApplicationStatus::NotPassed,
        ], true);
    }

    /** Progres 5 tahap akurat untuk portal (bukan persen palsu). */
    public function progressSteps(): array
    {
        $s = $this->application_status;
        $docBad = $this->documents()->where('status', 'needs_revision')->exists();
        return [
            ['key' => 'data', 'label' => 'Data Pendaftaran', 'state' => $s === ApplicationStatus::Draft ? 'current' : 'done'],
            ['key' => 'dokumen', 'label' => 'Dokumen', 'state' => $docBad ? 'attention' : ($s === ApplicationStatus::Draft ? 'todo' : 'done')],
            ['key' => 'verifikasi', 'label' => 'Verifikasi', 'state' => in_array($s, [ApplicationStatus::Submitted, ApplicationStatus::Resubmitted, ApplicationStatus::NeedsRevision], true) ? 'current' : (in_array($s, [ApplicationStatus::Draft], true) ? 'todo' : 'done')],
            ['key' => 'wawancara', 'label' => 'Wawancara', 'state' => in_array($s, [ApplicationStatus::WaitingSlot, ApplicationStatus::Scheduled], true) ? 'current' : (in_array($s, [ApplicationStatus::Interviewed, ApplicationStatus::WaitingDecision, ApplicationStatus::Passed, ApplicationStatus::NotPassed], true) ? 'done' : 'todo')],
            ['key' => 'hasil', 'label' => 'Hasil', 'state' => in_array($s, [ApplicationStatus::Passed, ApplicationStatus::NotPassed], true) ? 'done' : 'todo'],
        ];
    }

    /**
     * Satu sumber keputusan tahap/aksi portal. Blade hanya merender hasilnya.
     *
     * @return array{stage:string,label:string,route:string}
     */
    public function nextPortalAction(): array
    {
        $status = $this->application_status;

        if ($status === ApplicationStatus::Draft) {
            $gate = \App\Services\FinalSubmissionCheck::check($this);
            $dataSections = array_diff(array_keys($gate['missing_sections']), ['Dokumen']);
            if ($dataSections !== []) {
                return ['stage' => 'data', 'label' => 'Lanjutkan Data Pendaftaran', 'route' => route('portal.applications.edit', $this)];
            }
            $hasMissingDocument = $this->documents()->whereNull('path')->whereIn('type', ['kk', 'ktp_ortu', 'akta', 'rapor', 'foto'])->exists();
            return $hasMissingDocument
                ? ['stage' => 'dokumen', 'label' => 'Lengkapi Dokumen', 'route' => route('portal.applications.show', [$this, 'tahap' => 'dokumen'])]
                : ['stage' => 'verifikasi', 'label' => 'Review & Kirim', 'route' => route('portal.applications.review', $this)];
        }

        return match ($status) {
            ApplicationStatus::NeedsRevision => $this->documents()->where('status', 'needs_revision')->exists()
                ? ['stage' => 'dokumen', 'label' => 'Perbaiki Dokumen', 'route' => route('portal.applications.show', [$this, 'tahap' => 'dokumen'])]
                : ['stage' => 'data', 'label' => 'Perbaiki Data Pendaftaran', 'route' => route('portal.applications.edit', $this)],
            ApplicationStatus::Submitted, ApplicationStatus::Resubmitted => ['stage' => 'verifikasi', 'label' => 'Menunggu Verifikasi', 'route' => route('portal.applications.show', [$this, 'tahap' => 'verifikasi'])],
            ApplicationStatus::Verified, ApplicationStatus::WaitingSlot => ['stage' => 'wawancara', 'label' => 'Pilih Jadwal Wawancara', 'route' => route('portal.slots.index', $this)],
            ApplicationStatus::Scheduled => ['stage' => 'wawancara', 'label' => 'Lihat Jadwal', 'route' => route('portal.applications.show', [$this, 'tahap' => 'wawancara'])],
            ApplicationStatus::Interviewed, ApplicationStatus::WaitingDecision => ['stage' => 'hasil', 'label' => 'Menunggu Hasil', 'route' => route('portal.applications.show', [$this, 'tahap' => 'hasil'])],
            ApplicationStatus::Passed, ApplicationStatus::NotPassed => ['stage' => 'hasil', 'label' => 'Lihat Hasil', 'route' => route('portal.applications.show', [$this, 'tahap' => 'hasil'])],
            default => ['stage' => 'data', 'label' => 'Lihat Pendaftaran', 'route' => route('portal.applications.show', $this)],
        };
    }
}
