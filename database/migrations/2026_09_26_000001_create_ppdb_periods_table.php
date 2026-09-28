<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_periods', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year')->unique();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->boolean('is_open')->default(true);
            $table->string('status_override')->nullable();
            $table->text('announcement')->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->string('contact_info')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'is_archived']);
        });

        // Backfill dari ppdb_settings (single-row legacy) tanpa hapus data lama
        if (Schema::hasTable('ppdb_settings')) {
            $legacy = DB::table('ppdb_settings')->first();
            if ($legacy) {
                DB::table('ppdb_periods')->updateOrInsert(
                    ['academic_year' => $legacy->academic_year ?? '2026/2027'],
                    [
                        'opens_at' => $legacy->opens_at,
                        'closes_at' => $legacy->closes_at,
                        'is_open' => (bool) ($legacy->is_open ?? true),
                        'status_override' => $legacy->status_override,
                        'announcement' => $legacy->announcement,
                        'quota' => $legacy->quota,
                        'contact_info' => $legacy->contact_info,
                        'is_archived' => false,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } else {
                DB::table('ppdb_periods')->insert([
                    'academic_year' => '2026/2027',
                    'is_open' => true,
                    'is_archived' => false,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_periods');
    }
};
