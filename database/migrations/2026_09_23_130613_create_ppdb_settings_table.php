<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ppdb_settings', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year')->default('2026/2027');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->boolean('is_open')->default(true);
            $table->string('status_override')->nullable(); // null, open, closed
            $table->text('announcement')->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->string('contact_info')->nullable();
            $table->timestamps();
        });

        // seed default row
        DB::table('ppdb_settings')->insert([
            'academic_year' => '2026/2027',
            'is_open' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_settings');
    }
};
