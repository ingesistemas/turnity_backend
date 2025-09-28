<?php

namespace App\Models\empresasBases;

use Illuminate\Database\Eloquent\Model;

class PisosModel extends Model
{
    protected $connection = 'empresa_base';
    //use LogsActivity;
    protected $table = 'pisos';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'piso',
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
    
}
