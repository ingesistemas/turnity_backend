<?php

namespace App\Models\infoclic;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class empresaInfoclicModel extends Model
{
    protected $table = 'empresas_infoclic';

    // Campos que pueden ser asignados en masa
    protected $fillable = [
        'nit',
        'nombre',
        'tels',
        'activo',
        'id_usuario'
    ];

    public function aplicaciones()
    {
        return $this->belongsToMany(
            AplicacionModel::class,
            'empresas_aplicaciones',
            'id_empresa',
            'id_aplicacion'
        );
    }
    


}
