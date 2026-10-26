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
        Schema::table('listings', function (Blueprint $table) {
        // Schimbă tipul description din text în longText
        $table->longText('description')->change();
        // Adaugă proprietățile noi
        $table->unsignedInteger('views_count')->default(0)->after('status');
        $table->boolean('is_featured')->default(false)->after('views_count');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
        $table->text('description')->change();
        $table->dropColumn(['views_count', 'is_featured']);
    });
    }
};
