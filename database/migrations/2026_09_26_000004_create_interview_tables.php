<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->nullable()->constrained('ppdb_periods')->nullOnDelete();
            $table->date('date')->index();
            $table->string('start_time', 5);
            $table->string('end_time', 5)->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->default(1);
            $table->unsignedInteger('booked_count')->default(0);
            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['date', 'status']);
        });

        Schema::create('interview_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('ppdb_registrations')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('interview_slots')->restrictOnDelete();
            $table->string('status')->default('scheduled')->index();
            $table->timestamp('booked_at')->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->timestamps();
            $table->unique(['application_id']);
        });

        Schema::create('reschedule_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('interview_appointments')->cascadeOnDelete();
            $table->foreignId('old_slot_id')->constrained('interview_slots')->restrictOnDelete();
            $table->foreignId('new_slot_id')->nullable()->constrained('interview_slots')->nullOnDelete();
            $table->text('reason');
            $table->string('status')->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('interview_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('interview_appointments')->cascadeOnDelete();
            $table->string('attendance')->default('attended');
            $table->text('interview_notes')->nullable();
            $table->text('tahfizh_notes')->nullable();
            $table->text('tahsin_notes')->nullable();
            $table->string('recommendation')->nullable();
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_assessments');
        Schema::dropIfExists('reschedule_requests');
        Schema::dropIfExists('interview_appointments');
        Schema::dropIfExists('interview_slots');
    }
};
