<?php

namespace App\Http\Controllers\infoclic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;

use App\Models\infoclic\CiudadModel;
use Illuminate\Support\Facades\DB;

class DptosController extends respuestaController
{
    public function obtenerDptos(){
        $dptos = DB::table('dptos')
        ->select('dptos.id', 'dptos.cod_dep', 'dptos.departamento')
        ->distinct()
        ->get();

        $this->message = 'Dptos encontrados.';
        $this->status  = 200;
        $this->error = false;
        $this->data    = $dptos;
        
        return $this->respond();
    }

    public function obtenerCiudades(Request $request){
        $ciudades = CiudadModel::with('departamento')->get();
        if($ciudades->empty()){
            $this->message = 'No se encontraron ciudades registradas.';
        }else{
            $this->message = 'Ciudades encontradas.';
        }

        $this->message = $this->message;
        $this->status  = 200;
        $this->error = false;
        $this->data    = $ciudades;
        
        return $this->respond();
    
    }
}
