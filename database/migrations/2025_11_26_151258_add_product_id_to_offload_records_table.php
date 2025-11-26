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
        Schema::table('offload_records', function (Blueprint $table) {
            $table->unsignedBigInteger('productId')->nullable()->after('externalFactory');
            $table->foreign('productId')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offload_records', function (Blueprint $table) {
            $table->dropForeign(['productId']);
            $table->dropColumn('productId');
        });
    }
};
