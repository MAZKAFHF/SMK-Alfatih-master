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

        User::factory()->create([
            'name' => 'Admin Al-Fatih',
            'email' => 'admin@smkalfatih.sch.id',
            'password' => $adminPassword !== '' ? $adminPassword : 'admin1234',
            'is_admin' => true,
            'is_superadmin' => true,
        ]);

        $this->call([
            ProgramSeeder::class,
            PageSeeder::class,
            NewsSeeder::class,
            GallerySeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
