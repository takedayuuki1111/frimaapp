<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sold_items', function (Blueprint $table) {
            $table->string('status')->default('trading')->after('item_id');
            $table->unsignedTinyInteger('seller_rating')->nullable()->after('status');
            $table->unsignedTinyInteger('buyer_rating')->nullable()->after('seller_rating');
            $table->timestamp('completed_at')->nullable()->after('buyer_rating');
        });
    }

    public function down(): void
    {
        Schema::table('sold_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'seller_rating', 'buyer_rating', 'completed_at']);
        });
    }
};
