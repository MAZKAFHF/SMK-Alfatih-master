<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-superadmin {email?} {--name=}';
    protected $description = 'Create initial superadmin interactively (no hardcoded password)';

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Email superadmin');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email tidak valid.');
            return self::FAILURE;
        }
        if (\App\Models\User::where('email', $email)->exists()) {
            $this->error('Email sudah terdaftar.');
            return self::FAILURE;
        }
        $name = $this->option('name') ?? $this->ask('Nama lengkap', 'Admin Al-Fatih');
        $password = $this->secret('Password (min 8 karakter)');
        if (strlen($password) < 8) {
            $this->error('Password minimal 8 karakter.');
            return self::FAILURE;
        }
        $confirm = $this->secret('Konfirmasi password');
        if ($password !== $confirm) {
            $this->error('Konfirmasi tidak cocok.');
            return self::FAILURE;
        }

        $user = \App\Models\User::create([
            'name' => $name,
            'email' => strtolower(trim($email)),
            'password' => $password,
            'is_admin' => true,
            'is_superadmin' => true,
            'is_active' => true,
        ]);

        $this->info("Superadmin created: {$user->email} (ID {$user->id})");
        return self::SUCCESS;
    }
}
