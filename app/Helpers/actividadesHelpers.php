<?php

use App\Models\empresasBases\RegistrosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

    function log_actividades($tabla, $accion, Request $request, $cambios = [], $descripcion = null)
    {
        if($accion == 'Editar estado'){
            if($request->activo == 0){
                $cambios['activo'] = 1;
            }else{
                $cambios['activo'] = 0;
            }
        } 
        
        RegistrosModel::create([
            'id_usuario'     => $request->id_usuario,
            'tabla'  => $tabla,
            'id_registro'    => $request->id ?? null,
            'accion'      => $accion,
            'cambios'     => $cambios,
            'descripcion' => $descripcion,
        ]);
    }
