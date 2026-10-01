<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminPassword = (string) config('app.initial_admin_password');

        if (app()->environment('production') && $adminPassword === '') {
            throw new \RuntimeException('ADMIN_INITIAL_PASSWORD wajib dikonfigurasi sebelum menjalankan seeder di production.');
        }

        $admin = User::query()->firstOrNew([
            'email' => 'admin@smkalfatih.sch.id',
        ]);

        if (! $admin->exists) {
            $admin->name = 'Admin Al-Fatih';
            $admin->password = $adminPassword !== '' ? $adminPassword : 'admin1234';
            $admin->email_verified_at = now();
        }

        $admin->is_admin = true;
        $admin->is_superadmin = true;
        $admin->is_active = true;
        $admin->is_applicant = false;
        $admin->save();

        $this->call([
            ProgramSeeder::class,
            PageSeeder::class,
            NewsSeeder::class,
            GallerySeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
