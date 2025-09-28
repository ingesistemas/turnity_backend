<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\SucursalRequest;
use App\Models\empresasBases\PrioritariasModel;
use App\Traits\ValidaCentrosTrait;

class PrioritariasController extends respuestaController
{

    public function obtenerRegistros(){
        $prioritarias = PrioritariasModel::select(
            'id', 'prioritaria', 'created_at'
        )->get();

        if($prioritarias->isEmpty()){
            $this->message = 'No se encontraron prioritarias registradas en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $prioritarias; 
        return $this->respond();
    }
}
