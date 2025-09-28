<?php

namespace App\Models\empresasBases;

use Illuminate\Database\Eloquent\Model;


class RegistrosModel extends Model
{
    protected $connection = 'empresa_base';
    protected $table = 'registros_actividades';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'id',
        'id_usuario',
        'tabla',
        'id_registro',
        'accion',
        'cambios',
        'descripcion'
    ];

    protected $casts = [
        'cambios' => 'array',
    ];
}
