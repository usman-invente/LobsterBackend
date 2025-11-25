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
        Schema::create('loss_adjustments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('tankId');
            $table->integer('tankNumber');
            $table->enum('type', ['dead', 'rotten', 'lost']);
            $table->enum('size', ['U', 'A', 'B', 'C', 'D', 'E', 'M']);
            $table->decimal('kg', 10, 2);
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Indexes for better query performance
            // $table->index('date');
            // $table->index('tankNumber');
            // $table->index('type');
            // $table->index(['date', 'tankNumber']);
            // $table->index(['date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_adjustments');
    }
};
