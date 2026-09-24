<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PPDBRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ppdb_registrations';

    protected $fillable = [
        'registration_number',
        'name',
        'nisn',
        'birth_place',
        'birth_date',
        'gender',
        'address',
        'school_origin',
        'phone',
        'email',
        'parent_name',
        'program_id',
        'status',
        'admin_notes',
        'academic_year',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'status' => RegistrationStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PPDBRegistration $registration): void {
            if (is_null($registration->registration_number)) {
                $registration->registration_number = static::generateRegistrationNumberAtomic();
            }
            if (empty($registration->academic_year)) {
                $registration->academic_year = PpdbSetting::current()->academic_year;
            }
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
     * Concurrency-safe generation using transaction + table lock / max+1 with unique retry.
     * Retries up to 5 times if duplicate due to race.
     */
    public static function generateRegistrationNumberAtomic(): string
    {
        return DB::transaction(function () {
            // Lock for update on sqlite will serialize writes within transaction
            $year = now()->year;
            $max = static::where('registration_number', 'like', "PPDB-{$year}-%")
                ->lockForUpdate()
                ->max('registration_number');

            $next = 1;
            if ($max) {
                $num = (int) substr((string) $max, -5);
                $next = $num + 1;
            } else {
                // fallback global max for first of year
                $globalMax = static::withTrashed()->max('id') ?? 0;
                $next = $globalMax + 1;
                // Ensure not exceeding for year - if overflow, just use incremented num
                $existingYearMax = static::whereYear('created_at', $year)->count() + 1;
                $next = max($next, $existingYearMax);
            }

            $candidate = sprintf('PPDB-%s-%05d', $year, $next);
            // Ensure uniqueness with retry (rare race)
            $attempt = 0;
            while (static::withTrashed()->where('registration_number', $candidate)->exists() && $attempt < 5) {
                $next++;
                $candidate = sprintf('PPDB-%s-%05d', $year, $next);
                $attempt++;
            }

            return $candidate;
        }, 3);
    }

    public static function generateRegistrationNumber(): string
    {
        return static::generateRegistrationNumberAtomic();
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
}
