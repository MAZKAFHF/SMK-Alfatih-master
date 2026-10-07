<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\MailService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'admin_code',
        'admin_code_set_at',
        'phone',
        'last_activity_at',
        'is_admin',
        'is_superadmin',
        'is_active',
        'is_applicant',
        'retirement_due_at',
        'retirement_started_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'admin_code',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'password' => 'hashed',
            'admin_code' => 'hashed',
            'admin_code_set_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_superadmin' => 'boolean',
            'is_active' => 'boolean',
            'is_applicant' => 'boolean',
            'retirement_due_at' => 'datetime',
            'retirement_started_at' => 'datetime',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(PPDBRegistration::class, 'applicant_account_id');
    }

    public function applicationsWithTrashed(): HasMany
    {
        return $this->hasMany(PPDBRegistration::class, 'applicant_account_id')->withTrashed();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ApplicantNotification::class, 'account_id');
    }

    public function isApplicant(): bool
    {
        return (bool) $this->is_applicant && ! $this->is_admin;
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Send a branded, auditable password-reset email through the same
     * resilient delivery pipeline as every other PPDB transactional email.
     */
    public function sendPasswordResetNotification($token): void
    {
        $route = $this->is_admin ? 'admin.password.reset' : 'portal.password.reset';
        $resetUrl = route($route, [
            'token' => $token,
            'email' => $this->email,
        ]);

        MailService::send('reset_password', $this->email, 'Reset Password Akun SMK Tahfizh Al-Fatih', [
            'headline' => 'Reset Password Akun Anda',
            'preheader' => 'Gunakan tautan aman ini untuk membuat password baru.',
            'body' => '<p>Halo <strong>'.e($this->name).'</strong>,</p><p>Kami menerima permintaan reset password untuk akun Anda. Gunakan tombol berikut untuk membuat password baru. Tautan akan kedaluwarsa sesuai batas keamanan sistem.</p><p>Jika Anda tidak meminta reset password, abaikan email ini dan jangan bagikan tautannya kepada siapa pun.</p>',
            'cta' => 'Buat Password Baru',
            'cta_url' => $resetUrl,
        ]);
    }
}
