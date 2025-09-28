<?php

namespace App\Models\turnity;

use App\Models\empresasBases\SalasModel;
use App\Models\empresasBases\SucursalModel;
use App\Models\empresasBases\UsuarioModel;
use App\Models\infoclic\CiudadModel;
use Illuminate\Database\Eloquent\Model;

class CasosModel extends Model
{
    protected $connection = 'multi_empresa';
    //use LogsActivity;
    protected $table = 'casos';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'caso',
        'clase',
        'id_usuario',
        'created_at'
    ];


    // Si deseas ocultar campos en JSON (opcional)
    protected $hidden = [
        // 'created_at', 'updated_at',
    ];

    // Relación con usuarios (si usas roles con users)
    public function usuarios()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario');
    }

    public function sucursalPadre()
    {
        return $this->belongsTo(SucursalModel::class, 'id_sucursal');
    }

    public function sala()
    {
        return $this->belongsTo(SalasModel::class, 'id_sala');
    }

    public function asignaciones() {
        return $this->hasMany(AsignacionesModel::class, 'id_caso');
    }
    
}
