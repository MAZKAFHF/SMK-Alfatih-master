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
        foreach (['ppdb_registrations', 'news', 'pages', 'galleries', 'announcements', 'contact_messages', 'programs'] as $table) {
            Schema::table($table, function (Blueprint $tbl) {
                $tbl->softDeletes()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['programs', 'contact_messages', 'announcements', 'galleries', 'pages', 'news', 'ppdb_registrations'] as $table) {
            Schema::table($table, function (Blueprint $tbl) {
                $tbl->dropSoftDeletes();
            });
        }
    }
};
