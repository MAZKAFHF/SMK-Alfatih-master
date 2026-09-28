<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_periods', function (Blueprint $table) {
            $table->timestamp('results_released_at')->nullable()->after('closed_at');
            $table->timestamp('operational_completed_at')->nullable()->after('results_released_at');
            $table->timestamp('account_retention_until')->nullable()->index()->after('operational_completed_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('retirement_due_at')->nullable()->index()->after('is_applicant');
            $table->timestamp('retirement_started_at')->nullable()->after('retirement_due_at');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('actor_type', 20)->nullable()->index()->after('user_id');
        });

        Schema::table('login_logs', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', fn (Blueprint $table) => $table->dropIndex(['created_at']));
        Schema::table('audit_logs', fn (Blueprint $table) => $table->dropColumn('actor_type'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['retirement_due_at', 'retirement_started_at']));
        Schema::table('ppdb_periods', fn (Blueprint $table) => $table->dropColumn([
            'results_released_at', 'operational_completed_at', 'account_retention_until',
        ]));
    }
};
