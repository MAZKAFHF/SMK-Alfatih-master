<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->string('provider', 30)->nullable()->after('status');
            $table->string('provider_message_id')->nullable()->unique()->after('provider');
            $table->timestamp('delivered_at')->nullable()->after('sent_at');
            $table->timestamp('bounced_at')->nullable()->after('delivered_at');
            $table->timestamp('complained_at')->nullable()->after('bounced_at');
            $table->timestamp('last_event_at')->nullable()->index()->after('complained_at');
        });

        Schema::create('email_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->default('resend');
            $table->string('event_id')->unique();
            $table->string('event_type')->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('processed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_webhook_events');

        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropUnique(['provider_message_id']);
            $table->dropIndex(['last_event_at']);
            $table->dropColumn([
                'provider', 'provider_message_id', 'delivered_at', 'bounced_at',
                'complained_at', 'last_event_at',
            ]);
        });
    }
};
