<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SATU sumber kebenaran status: kolom `status`
     * (draft/upcoming/open/closed/archived).
     * Kolom boolean lama (is_open/is_active/is_archived) dipertahankan
     * read-only untuk kompatibilitas, diabaikan logika baru.
     */
    public function up(): void
    {
        Schema::table('ppdb_periods', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('academic_year');
            $table->timestamp('closed_at')->nullable()->after('closes_at');
            $table->foreignId('created_by')->nullable()->after('contact_info')->constrained('users')->nullOnDelete();
            $table->index(['status', 'id']);
        });

        // Backfill status dari kondisi kini (non-destruktif).
        foreach (DB::table('ppdb_periods')->get() as $p) {
            $status = 'draft';
            if ($p->is_archived) {
                $status = 'archived';
            } elseif (($p->status_override ?? null) === 'open') {
                $status = 'open';
            } elseif (($p->status_override ?? null) === 'closed') {
                $status = 'closed';
            } elseif (! $p->is_open || ! $p->is_active) {
                $status = 'closed';
            } elseif ($p->opens_at && now()->lt($p->opens_at)) {
                $status = 'upcoming';
            } elseif ($p->closes_at && now()->gt($p->closes_at)) {
                $status = 'closed';
            } else {
                $status = 'open';
            }
            DB::table('ppdb_periods')->where('id', $p->id)->update(['status' => $status]);
        }

        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->index(['period_id', 'status'], 'ppdb_reg_period_status_idx');
            $table->index(['period_id', 'program_id'], 'ppdb_reg_period_program_idx');
            $table->index(['period_id', 'created_at'], 'ppdb_reg_period_created_idx');
        });

        Schema::table('interview_slots', function (Blueprint $table) {
            $table->index(['period_id', 'date'], 'interview_slots_period_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('interview_slots', function (Blueprint $table) {
            $table->dropIndex('interview_slots_period_date_idx');
        });
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropIndex('ppdb_reg_period_status_idx');
            $table->dropIndex('ppdb_reg_period_program_idx');
            $table->dropIndex('ppdb_reg_period_created_idx');
        });
        Schema::table('ppdb_periods', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['status', 'closed_at']);
        });
    }
};
