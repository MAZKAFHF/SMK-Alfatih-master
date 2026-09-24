<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->index()->after('is_superadmin');
        });

        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->text('admin_notes')->nullable()->after('status');
            $table->string('academic_year')->nullable()->index()->after('admin_notes');
            $table->index(['status', 'program_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropColumn(['admin_notes', 'academic_year']);
            $table->dropIndex(['ppdb_registrations_status_program_id_index']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
