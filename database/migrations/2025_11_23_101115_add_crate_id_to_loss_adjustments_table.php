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
        Schema::table('loss_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('crateId')->nullable()->after('tankId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_adjustments', function (Blueprint $table) {
             $table->dropColumn('crateId');
        });
    }
};
