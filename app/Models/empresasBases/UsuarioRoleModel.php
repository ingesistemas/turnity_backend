<?php

namespace App\Models\empresasBases;
use Illuminate\Database\Eloquent\Model;


class UsuarioRoleModel extends Model
{
    protected $connection = 'empresa_base';
    protected $table = 'usuarios_roles';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'id',
        'id_usuario',
        'id_rol'
    ];


    // Si deseas ocultar campos en JSON (opcional)
    protected $hidden = [
        // 'created_at', 'updated_at',
    ];


}
