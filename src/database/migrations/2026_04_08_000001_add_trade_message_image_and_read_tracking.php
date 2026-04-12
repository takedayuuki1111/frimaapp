<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sold_items', function (Blueprint $table) {
            $table->timestamp('seller_last_read_at')->nullable()->after('completed_at');
            $table->timestamp('buyer_last_read_at')->nullable()->after('seller_last_read_at');
        });

        Schema::table('trade_messages', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('trade_messages', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('sold_items', function (Blueprint $table) {
            $table->dropColumn(['seller_last_read_at', 'buyer_last_read_at']);
        });
    }
};
