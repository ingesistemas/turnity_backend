<?php

namespace App\Models\infoclic;

use Illuminate\Database\Eloquent\Model;


class DepartamentoModel extends Model
{
    protected $table = 'dptos';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'id',
        'cod_dep',
        'departamento'
    ];

}
