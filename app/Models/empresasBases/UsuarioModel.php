<?php

namespace App\Models\empresasBases;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UsuarioModel extends Model implements JWTSubject
{
    protected $connection = 'empresa_base';
    //use LogsActivity|;
    protected $table = 'usuarios';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'id',
        'nombre',
        'usuario',
        'password',
        'celular',
        'email',
        'activo',
        'id_profesion',
        'id_sucursal',
        'id_usuario',
        'created_at'
    ];

    // Si deseas ocultar campos en JSON (opcional)
    protected $hidden = [
      /*  'usuario',
       'password' */
    ];

     public function roles()
    {
        return $this->belongsToMany(\App\Models\empresasBases\RoleModel::class, 'usuarios_roles', 'id_usuario', 'id_rol');
    }

    public function sucursales()
    {
        return $this->belongsToMany(\App\Models\empresasBases\SucursalModel::class, 'usuarios_sucursales', 'id_usuario', 'id_sucursal');
        //->setConnection('empresa_base') ;
    }

    public function profesion()
    {
        return $this->belongsTo(\App\Models\empresasBases\ProfesionModel::class, 'id_profesion');
    }
    

    public function creador()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey(); // Usualmente el ID del usuario
    }

    public function getJWTCustomClaims()
    {
        return []; // Puedes agregar datos adicionales al token si lo deseas
    }
}
