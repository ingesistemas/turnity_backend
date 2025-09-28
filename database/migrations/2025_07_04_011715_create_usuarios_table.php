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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('usuario', 20);
            $table->string('password');
            $table->string('celular' , 10);
            $table->string('email');
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_sucursal');
            $table->integer('activo');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
