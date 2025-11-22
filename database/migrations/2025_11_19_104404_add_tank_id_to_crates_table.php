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
        Schema::table('crates', function (Blueprint $table) {
             $table->foreignId('tankId')->nullable()->after('status')->constrained('tanks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crates', function (Blueprint $table) {
             $table->dropForeign(['tankId']);
            $table->dropColumn('tankId');
        });
    }
};
