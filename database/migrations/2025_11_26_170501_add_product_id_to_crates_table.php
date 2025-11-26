<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crates', function (Blueprint $table) {
            $table->unsignedBigInteger('productId')->nullable()->after('user_id');
            $table->foreign('productId')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::table('crates', function (Blueprint $table) {
            $table->dropForeign(['productId']);
            $table->dropColumn('productId');
        });
    }
};

