<?php

namespace App\Models\empresasBases;

use App\Models\empresasBases\UsuarioModel;
use Illuminate\Database\Eloquent\Model;


class RoleModel extends Model
{
    protected $connection = 'empresa_base';
    protected $table = 'roles';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'rol',
        'descripcion',
        'activo',
        'id_usuario'
    ];


    // Si deseas ocultar campos en JSON (opcional)
    protected $hidden = [
        // 'created_at', 'updated_at',
    ];

    // Relación con usuarios (si usas roles con users)
    public function usuarios()
    {
       return $this->belongsToMany(UsuarioModel::class);
    }
}
