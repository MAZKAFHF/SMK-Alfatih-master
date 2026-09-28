<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->foreignId('applicant_account_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('period_id')->nullable()->after('applicant_account_id')->constrained('ppdb_periods')->nullOnDelete();
            $table->string('application_status')->default('draft')->after('status')->index();
            $table->string('source')->default('applicant')->after('application_status')->index();
            $table->foreignId('created_by')->nullable()->after('source')->constrained('users')->nullOnDelete();
            $table->string('nik', 25)->nullable()->after('name');
            $table->string('photo_path')->nullable()->after('nik');
            // Alamat terstruktur (address lama tetap untuk kompat)
            $table->string('province', 100)->nullable()->after('address');
            $table->string('city', 100)->nullable()->after('province');
            $table->string('district', 100)->nullable()->after('city');
            $table->string('village', 100)->nullable()->after('district');
            $table->string('postal_code', 10)->nullable()->after('village');
            // Ortu / wali terstruktur (parent_name lama tetap)
            $table->string('father_name', 150)->nullable()->after('parent_name');
            $table->string('father_phone', 30)->nullable()->after('father_name');
            $table->string('father_occupation', 100)->nullable()->after('father_phone');
            $table->string('mother_name', 150)->nullable()->after('father_occupation');
            $table->string('mother_phone', 30)->nullable()->after('mother_name');
            $table->string('mother_occupation', 100)->nullable()->after('mother_phone');
            $table->string('guardian_name', 150)->nullable()->after('mother_occupation');
            $table->string('guardian_phone', 30)->nullable()->after('guardian_name');
            $table->string('guardian_relation', 50)->nullable()->after('guardian_phone');
            $table->unsignedInteger('sequence')->nullable()->after('registration_number');
            $table->timestamp('submitted_at')->nullable()->after('sequence');
            $table->timestamp('verified_at')->nullable()->after('submitted_at');
            // NPSN + tahun lulus (KONFIRMASI sekolah, nullable)
            $table->string('school_npsn', 30)->nullable()->after('school_origin');
            $table->string('graduation_year', 9)->nullable()->after('school_npsn');

            $table->index(['period_id', 'application_status']);
            $table->index(['applicant_account_id', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('applicant_account_id');
            $table->dropConstrainedForeignId('period_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'application_status', 'source', 'nik', 'photo_path',
                'province', 'city', 'district', 'village', 'postal_code',
                'father_name', 'father_phone', 'father_occupation',
                'mother_name', 'mother_phone', 'mother_occupation',
                'guardian_name', 'guardian_phone', 'guardian_relation',
                'sequence', 'submitted_at', 'verified_at',
                'school_npsn', 'graduation_year',
            ]);
        });
    }
};
