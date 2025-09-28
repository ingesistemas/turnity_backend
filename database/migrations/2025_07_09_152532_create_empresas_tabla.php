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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nit', 30);
            $table->string('nombre', 150);
            $table->unsignedBigInteger('id_ciudad');
            $table->string('direccion', 150);
            $table->string('lema')->nullable();
            $table->string('web')->nullable();
            $table->string('email')->nullable();
            $table->string('tels')->nullable();
            $table->unsignedBigInteger('activo');
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas_tabla');
    }
};
