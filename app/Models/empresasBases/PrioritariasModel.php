<?php

namespace App\Models\empresasBases;

use App\Models\infoclic\CiudadModel;
use App\Models\turnity\TurnosModel;
use Illuminate\Database\Eloquent\Model;

class PrioritariasModel extends Model
{
    protected $connection = 'empresa_base';
    //use LogsActivity;
    protected $table = 'prioritarias';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'prioritaria',
        'created_at'
    ];


    // Si deseas ocultar campos en JSON (opcional)
    protected $hidden = [
        // 'created_at', 'updated_at',
    ];

    // Relación con usuarios (si usas roles con users)
    

    public function turnos()
    {
        return $this->hasMany(TurnosModel::class, 'id_prioritaria');
    }
    
}
