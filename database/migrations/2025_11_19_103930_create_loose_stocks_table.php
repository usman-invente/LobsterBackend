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
        Schema::create('loose_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tankId')->nullable()->constrained('tanks')->onDelete('set null');
            $table->enum('size', ['U', 'A', 'B', 'C', 'D', 'E']);
            $table->decimal('kg', 10, 2);
            $table->foreignId('fromCrateId')->nullable()->constrained('crates')->onDelete('set null');
            $table->string('boatName')->nullable();
            $table->date('offloadDate')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Add indexes for better query performance
            // $table->index('tankId');
            // $table->index('size');
            // $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loose_stocks');
    }
};
