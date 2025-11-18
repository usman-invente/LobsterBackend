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
        Schema::create('receiving_batches', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('batchNumber')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

         Schema::create('crates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_batch_id')->constrained()->onDelete('cascade');
            $table->string('boatName');
            $table->date('offloadDate');
            $table->integer('crateNumber')->unique();
            $table->enum('size', ['U', 'A', 'B', 'C', 'D', 'E']);
            $table->decimal('kg', 10, 2);
            $table->decimal('originalKg', 10, 2);
            $table->enum('originalSize', ['U', 'A', 'B', 'C', 'D', 'E']);
            $table->enum('status', ['received', 'stored', 'dispatched'])->default('received');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crates');
        Schema::dropIfExists('receiving_batches');
    }
};
