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
    Schema::create('stat_events', function (Blueprint $table) {
        $table->id();
        
        // Legăturile opționale: un vizitator poate fi anonim
        $table->foreignId('viewer_id')->nullable()->constrained('users')->nullOnDelete();
        
        // Exact una dintre acestea va fi completată pentru un eveniment
        $table->foreignId('listing_id')->nullable()->constrained('listings')->cascadeOnDelete();
        $table->foreignId('request_id')->nullable()->constrained('requests')->cascadeOnDelete();
        
        $table->string('eventType'); // ex: 'afisare', 'vizualizare', 'contactare'
        $table->string('searchQuery')->nullable(); // Ce a căutat utilizatorul
        $table->integer('resultPosition')->nullable(); // Poziția în rezultate
        
        $table->timestamps(); // Include createdAt
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stat_events');
    }
};
