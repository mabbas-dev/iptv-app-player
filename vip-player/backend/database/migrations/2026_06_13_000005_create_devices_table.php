<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_code', 17)->unique(); // MAC-style A1:B2:C3:D4:E5:F6
            $table->uuid('device_uuid')->unique();
            $table->string('platform')->default('android'); // android | android_tv
            $table->string('app_version')->nullable();
            $table->foreignId('reseller_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('new'); // new | trial | active | expired | blocked | suspended
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->string('parental_pin_hash')->nullable();
            $table->boolean('parental_lock_enabled')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
