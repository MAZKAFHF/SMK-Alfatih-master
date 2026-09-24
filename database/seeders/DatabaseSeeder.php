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
        User::factory()->create([
            'name' => 'Admin Al-Fatih',
            'email' => 'admin@smkalfatih.sch.id',
            'password' => 'admin1234',
            'is_admin' => true,
            'is_superadmin' => true,
        ]);

        $this->call([
            ProgramSeeder::class,
            PageSeeder::class,
            NewsSeeder::class,
            GallerySeeder::class,
            AnnouncementSeeder::class,
            PPDBSeeder::class,
        ]);
    }
}
