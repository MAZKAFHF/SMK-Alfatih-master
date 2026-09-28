<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('ppdb_registrations')->cascadeOnDelete();
            $table->string('result')->index();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('internal_note')->nullable();
            $table->text('applicant_message')->nullable();
            $table->timestamp('released_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('ppdb_registrations')->cascadeOnDelete();
            $table->string('section');
            $table->string('field')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason');
            $table->string('status')->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('internal_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('ppdb_registrations')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('ppdb_registrations')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['application_id', 'created_at']);
        });

        Schema::create('applicant_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('ppdb_registrations')->nullOnDelete();
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->index(['account_id', 'read_at']);
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template')->index();
            $table->string('recipient')->index();
            $table->string('subject')->nullable();
            $table->foreignId('application_id')->nullable()->constrained('ppdb_registrations')->nullOnDelete();
            $table->string('status')->default('sent')->index();
            $table->text('error')->nullable();
            $table->unsignedInteger('retries')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('applicant_notifications');
        Schema::dropIfExists('status_histories');
        Schema::dropIfExists('internal_notes');
        Schema::dropIfExists('change_requests');
        Schema::dropIfExists('application_decisions');
    }
};
