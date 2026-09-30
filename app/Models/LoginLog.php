<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    use HasFactory;

    public const EVENT_LOGIN = 'login';

    public const EVENT_LOGOUT = 'logout';

    public const EVENT_LOGIN_FAILED = 'login_failed';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'channel',
        'attempted_email',
        'failure_reason',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            self::EVENT_LOGIN => 'Masuk',
            self::EVENT_LOGOUT => 'Keluar',
            self::EVENT_LOGIN_FAILED => 'Gagal masuk',
            default => ucfirst($this->event),
        };
    }

    public function failureReasonLabel(): ?string
    {
        return match ($this->failure_reason) {
            'invalid_credentials' => 'Kredensial tidak cocok',
            'inactive_account' => 'Akun dinonaktifkan',
            'rate_limited' => 'Terlalu banyak percobaan',
            default => $this->failure_reason,
        };
    }
}
