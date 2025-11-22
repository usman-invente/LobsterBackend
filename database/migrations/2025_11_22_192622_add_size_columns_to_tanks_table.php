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
        Schema::table('tanks', function (Blueprint $table) {
            $table->decimal('sizeU', 10, 2)->default(0)->after('status');
            $table->decimal('sizeA', 10, 2)->default(0)->after('sizeU');
            $table->decimal('sizeB', 10, 2)->default(0)->after('sizeA');
            $table->decimal('sizeC', 10, 2)->default(0)->after('sizeB');
            $table->decimal('sizeD', 10, 2)->default(0)->after('sizeC');
            $table->decimal('sizeE', 10, 2)->default(0)->after('sizeD');
            $table->decimal('totalKg', 10, 2)->default(0)->after('sizeE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanks', function (Blueprint $table) {
              $table->dropColumn(['sizeU', 'sizeA', 'sizeB', 'sizeC', 'sizeD', 'sizeE', 'totalKg']);
        });
    }
};
