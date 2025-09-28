<?php

namespace App\Models\turnity;

use App\Http\Controllers\turnity\TurnosController;
use App\Models\empresasBases\SalasModel;
use App\Models\empresasBases\SucursalModel;
use App\Models\empresasBases\UsuarioModel;
use App\Models\infoclic\CiudadModel;
use Illuminate\Database\Eloquent\Model;

class LlamadosModel extends Model
{
    protected $connection = 'multi_empresa';
    //use LogsActivity;
    protected $table = 'llamados';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'id_asigna',
        'hora_llamado',
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

    public function turno() {
        return $this->belongsTo(TurnosModel::class, 'id_turno');
    } 
    
}
