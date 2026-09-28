<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('handling_status')->default('new')->index()->after('is_archived');
            $table->string('response_channel', 30)->nullable()->after('handling_status');
            $table->text('admin_note')->nullable()->after('response_channel');
            $table->foreignId('resolved_by')->nullable()->after('admin_note')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
        });
        Schema::table('email_logs', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('subject');
        });
    }

    public function down(): void
    {
        Schema::table('email_logs', fn (Blueprint $table) => $table->dropColumn('payload'));
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropColumn(['handling_status', 'response_channel', 'admin_note', 'resolved_at']);
        });
    }
};
