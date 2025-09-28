<?php
namespace App\Models\infoclic;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AplicacionModel extends Model
{
    use LogsActivity;

    protected $table = 'aplicaciones'; // ✅ tabla correcta

    protected $fillable = [
        'id',
        'aplicacion',
        'id_empresa',
        'activo',
    ];

    public function empresas()
    {
        return $this->belongsToMany(
            empresaInfoclicModel::class,
            'empresas_aplicaciones',
            'id_aplicacion',
            'id_empresa'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rol', 'descripcion', 'activo'])
            ->useLogName('roles')
            ->setDescriptionForEvent(fn(string $eventName) => "Rol {$eventName}")
            ->logOnlyDirty();
    }

    protected $hidden = [
        // 'created_at', 'updated_at',
    ];
}
