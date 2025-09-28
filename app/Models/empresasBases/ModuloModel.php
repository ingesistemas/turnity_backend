<?php

namespace App\Models\empresasBases;

use App\Models\infoclic\CiudadModel;
use Illuminate\Database\Eloquent\Model;

class ModuloModel extends Model
{
    protected $connection = 'empresa_base';
    //use LogsActivity;
    protected $table = 'modulos';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'modulo',
        'id_sala',
        'activo',
        'id_usuario',
        'id_sucursal',
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

    public function ciudades()
    {
        return $this->belongsTo(CiudadModel::class, 'id_ciudad', 'id');
    }


    public function sala()
    {
        return $this->belongsTo(SalasModel::class, 'id_sala');
    }
    
}
