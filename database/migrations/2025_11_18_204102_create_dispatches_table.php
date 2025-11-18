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
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['export', 'regrade']);
            $table->string('clientAwb');
            $table->date('dispatchDate');
            $table->decimal('totalKg', 10, 2);
            $table->decimal('sizeU', 10, 2)->default(0);
            $table->decimal('sizeA', 10, 2)->default(0);
            $table->decimal('sizeB', 10, 2)->default(0);
            $table->decimal('sizeC', 10, 2)->default(0);
            $table->decimal('sizeD', 10, 2)->default(0);
            $table->decimal('sizeE', 10, 2)->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dispatch_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained()->onDelete('cascade');
            $table->string('tankId');
            $table->integer('tankNumber');
            $table->string('crateId')->nullable();
            $table->string('looseStockId')->nullable();
            $table->enum('size', ['U', 'A', 'B', 'C', 'D', 'E']);
            $table->decimal('kg', 10, 2);
            $table->integer('crateNumber')->nullable();
            $table->boolean('isLoose')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_line_items');
        Schema::dropIfExists('dispatches');
    }
};
