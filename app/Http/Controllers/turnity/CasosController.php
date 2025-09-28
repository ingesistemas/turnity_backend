<?php

namespace App\Http\Controllers\turnity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\SucursalRequest;
use App\Models\empresasBases\CentrosModel;
use App\Models\empresasBases\ClienteModel;
use App\Models\empresasBases\PisosModel;
use App\Models\empresasBases\SalasModel;
use App\Models\empresasBases\SucursalModel;
use App\Models\turnity\AsignacionesModel;
use App\Models\turnity\CasosModel;
use App\Models\turnity\TurnosModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class CasosController extends respuestaController
{
    public function obtenerRegistros(){
        
       $casos = CasosModel::all();


        if($casos->isEmpty()){
            $this->message = 'No se encontraron casos registrados en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $casos; 
        return $this->respond(); 
    }

    

    
}
