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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Usuario que hizo el cambio
            $table->string('table_name');                      // Nombre de la tabla modificada
            $table->unsignedBigInteger('record_id');           // ID del registro modificado
            $table->string('event');                           // Tipo de evento: created, updated, deleted
            $table->json('old_values')->nullable();            // Datos antes del cambio
            $table->json('new_values')->nullable();            // Datos después del cambio
            $table->timestamps();                              // created_at = cuándo ocurrió el cambio

            $table->index('user_id');
            $table->index('table_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
        
    }
};
