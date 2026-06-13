<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->boolean('mac_locked')->default(false)->after('parental_lock_enabled');
            $table->timestamp('playlist_synced_at')->nullable()->after('last_seen_at');
            $table->boolean('is_lifetime')->default(false)->after('subscription_ends_at');
        });

        Schema::table('playlists', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->timestamp('uploaded_at')->nullable()->after('is_active');
        });

        Schema::table('resellers', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('status');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('plan_type')->default('reseller')->after('name'); // reseller | customer
            $table->decimal('price_usd', 10, 2)->nullable()->after('credit_cost');
            $table->boolean('is_lifetime')->default(false)->after('is_trial');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['plan_type', 'price_usd', 'is_lifetime']);
        });

        Schema::table('resellers', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });

        Schema::table('playlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('device_id');
            $table->dropColumn('uploaded_at');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['mac_locked', 'playlist_synced_at', 'is_lifetime']);
        });
    }
};
