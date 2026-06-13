<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // xtream | m3u | m3u8 | direct
            $table->string('server_url')->nullable();   // xtream
            $table->string('username')->nullable();     // xtream
            $table->string('password')->nullable();     // xtream
            $table->text('url')->nullable();            // m3u / m3u8 / direct
            $table->string('file_path')->nullable();    // uploaded playlist file
            $table->string('epg_url')->nullable();
            $table->foreignId('reseller_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
