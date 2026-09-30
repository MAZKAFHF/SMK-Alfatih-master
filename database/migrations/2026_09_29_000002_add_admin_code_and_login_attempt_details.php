<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('admin_code')->nullable()->after('password');
            $table->timestamp('admin_code_set_at')->nullable()->after('admin_code');
        });

        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('login_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('attempted_email', 255)->nullable()->after('event')->index();
            $table->string('failure_reason', 50)->nullable()->after('attempted_email');
            $table->text('user_agent')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropIndex(['attempted_email']);
            $table->dropColumn(['attempted_email', 'failure_reason']);
            $table->string('user_agent')->nullable()->change();
            $table->dropForeign(['user_id']);
        });
        Schema::table('login_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin_code', 'admin_code_set_at']);
        });
    }
};
