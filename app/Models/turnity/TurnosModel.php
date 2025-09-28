<?php

namespace App\Models\empresasBases;
namespace App\Models\turnity;

use App\Models\empresasBases\ClienteModel;
use App\Models\empresasBases\ModuloModel;
use App\Models\empresasBases\PrioritariasModel;
use App\Models\empresasBases\SalasModel;
use App\Models\empresasBases\SucursalModel;
use App\Models\empresasBases\UsuarioModel;
use App\Models\infoclic\CiudadModel;
use Illuminate\Database\Eloquent\Model;

class TurnosModel extends Model
{
    protected $connection = 'multi_empresa';
    //use LogsActivity;
    protected $table = 'turnos';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'fecha',
        'id_paciente',
        'hora_llegada',
        'hora_asignacion',
        'id_sala',
        'id_prioritaria',
        'id_usuario',
        'hora_cita',
        'id_sucursal',
        'hora_ini',
        'hora_fin',
        'id_caso_turno',
        'id_modulo',
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
        return $this->hasMany(AsignacionesModel::class, 'id_turno');
    }

    public function paciente()
    {
        return $this->belongsTo(ClienteModel::class, 'id_paciente');
    }

    public function prioritaria()
    {
        return $this->belongsTo(PrioritariasModel::class, 'id_prioritaria');
    }

    public function destino()
    {
        return $this->belongsTo(CasosModel::class, 'id_caso_turno');
    } 

    public function creador()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario');
    }
}
