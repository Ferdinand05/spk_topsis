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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alternative_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('criteria_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->float('value');

            $table->timestamps();

            // biar tidak duplicate
            $table->unique(['alternative_id', 'criteria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
