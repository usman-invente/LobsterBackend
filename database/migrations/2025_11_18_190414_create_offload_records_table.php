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
        Schema::create('offload_records', function (Blueprint $table) {
          $table->id();
            $table->date('date');
            $table->string('boatName');
            $table->string('boatNumber');
            $table->string('captainName');
            $table->integer('totalCrates');
            $table->decimal('totalKgAlive', 10, 2);
            $table->decimal('sizeU', 10, 2)->default(0);
            $table->decimal('sizeA', 10, 2)->default(0);
            $table->decimal('sizeB', 10, 2)->default(0);
            $table->decimal('sizeC', 10, 2)->default(0);
            $table->decimal('sizeD', 10, 2)->default(0);
            $table->decimal('sizeE', 10, 2)->default(0);
            $table->decimal('deadOnTanks', 10, 2)->default(0);
            $table->decimal('rottenOnTanks', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Indexes for better query performance
            // $table->index('date');
            // $table->index('boatNumber');
            // $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offload_records');
    }
};
