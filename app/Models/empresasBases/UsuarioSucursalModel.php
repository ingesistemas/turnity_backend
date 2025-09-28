<?php

namespace App\Models\empresasBases;

use App\Models\infoclic\CiudadModel;
use Illuminate\Database\Eloquent\Model;

class UsuarioSucursalModel extends Model
{
    protected $connection = 'empresa_base';
    //use LogsActivity;
    protected $table = 'usuarios_sucursales';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'id_usuario',
        'id_sucursal'
    ];


    // Si deseas ocultar campos en JSON (opcional)
    protected $hidden = [
        // 'created_at', 'updated_at',
    ];

    public function usuarios()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario');
    }

    public function sucursales()
    {
        return $this->belongsTo(SucursalModel::class, 'id_sucursal');
    }

}
