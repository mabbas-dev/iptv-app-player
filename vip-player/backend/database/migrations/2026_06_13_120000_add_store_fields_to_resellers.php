<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resellers', function (Blueprint $table) {
            $table->string('store_name')->nullable()->after('company_name');
            $table->string('store_slug')->nullable()->unique()->after('store_name');
            $table->string('store_image')->nullable()->after('store_slug');
            $table->text('store_description')->nullable()->after('store_image');
            $table->string('store_url')->nullable()->after('store_description');
            $table->string('store_whatsapp')->nullable()->after('store_url');
            $table->string('store_email')->nullable()->after('store_whatsapp');
            $table->boolean('show_in_directory')->default(false)->after('store_email');
        });
    }

    public function down(): void
    {
        Schema::table('resellers', function (Blueprint $table) {
            $table->dropColumn([
                'store_name',
                'store_slug',
                'store_image',
                'store_description',
                'store_url',
                'store_whatsapp',
                'store_email',
                'show_in_directory',
            ]);
        });
    }
};
