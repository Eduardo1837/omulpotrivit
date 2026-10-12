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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
        
            $table->text('message');
            $table->decimal('proposedPrice', 10, 2);
            $table->string('status')->default('in_asteptare');

            $table->timestamps();

            // Restricție: un prestator poate trimite cel mult o ofertă pentru o anumită cerere
            $table->unique(['request_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
