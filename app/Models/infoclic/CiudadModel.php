<?php

namespace App\Models\infoclic;

use Illuminate\Database\Eloquent\Model;


class CiudadModel extends Model
{
    protected $table = 'ciudades';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'id',
        'id_dep',
        'cod_ciu',
        'ciudad'
    ];

    public function departamento()
    {
        return $this->belongsTo(DepartamentoModel::class, 'id_dep', 'id');
    }
    
}
