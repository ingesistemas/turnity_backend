<?php

namespace App\Models\turnity;

use App\Models\empresasBases\ModuloModel;
use App\Models\empresasBases\SalasModel;
use App\Models\empresasBases\SucursalModel;
use App\Models\empresasBases\UsuarioModel;
use App\Models\turnity\CasosModel;

use App\Models\infoclic\CiudadModel;
use Illuminate\Database\Eloquent\Model;

class AsignacionesModel extends Model
{
    protected $connection = 'multi_empresa';
    //use LogsActivity;
    protected $table = 'asignaciones';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'id_turno',
        'id_modulo',
        'id_operario',
        'id_operario_rea',
        'fecha',
        'hora_asigna',
        'hora_ini',
        'hora_fin',
        'hora_ope_ini',
        'hora_ope_fin',
        'id_caso',
        'id_sala',
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

    public function operario()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_operario');
    }

    public function sucursalPadre()
    {
        return $this->belongsTo(SucursalModel::class, 'id_sucursal');
    }

    public function sala()
    {
        return $this->belongsTo(SalasModel::class, 'id_sala');
    }

    public function turno() {
        return $this->belongsTo(TurnosModel::class, 'id_turno');
    }

    public function caso() {
        return $this->belongsTo(CasosModel::class, 'id_caso');
    }

    public function operarioProfesion()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_operario')->with('profesion');
    }

    public function modulo()
    {
        // Asignaciones.id_modulo se relaciona con ModuloModel.id
        return $this->belongsTo(ModuloModel::class, 'id_modulo');
    }

    public function llamados()
    {
        return $this->hasMany(LlamadosModel::class, 'id_asigna');
    }

     public function operarioReal()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_operario_rea');
    }
     
}
