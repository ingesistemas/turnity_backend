<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Models\empresasBases\PisosModel;
use App\Models\empresasBases\SucursalModel;
use App\Traits\ValidaPisosTrait;

class PisosController extends respuestaController
{
    use ValidaPisosTrait;
    public function obtenerRegistros(){
        $pisos = PisosModel::select(
        'id', 'piso', 'id_usuario', 'id_sucursal', 'created_at', 'activo')
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal',
        ])->get();

        if($pisos->isEmpty()){
            $this->message = 'No se encontraron pisos registradas en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $pisos; 
        return $this->respond();
    }

    public function obtenerRegistrosSucursal(Request $request){
        $pisos = PisosModel::select(
        'id', 'piso', 'id_usuario', 'id_sucursal', 'created_at', 'activo')
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal',
        ])->where('id_sucursal', $request->id_sucursal)
        ->get();

        if($pisos->isEmpty()){
            $this->message = 'No se encontraron pisos registradas en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $pisos; 
        return $this->respond();
    }

    public function crearRegistro(Request $request){
        $validator = $this->validarDatosPisos($request->all(), false, $request->id_sucursal);
        if ($validator->fails()) {
            $this->message = $validator->errors();
            $this->status  = 200;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }

        try {
            $piso = PisosModel::create([
                'piso'      => $request->piso,
                'activo'        => 0,
                'id_sucursal'   => $request->id_sucursal,
                'id_usuario'    => $request->id_usuario,
            ]);

            $this->message = 'El registro fue creado correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $piso;

        } catch (\Throwable $e) {
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
        }
        return $this->respond(); 
    }

    public function editarRegistro(Request $request){
        try{
            $validator = $this->validarDatosPisos($request->all(), true, $request->id_sucursal);

           log_actividades('pisos', 'Editar', $request, $request->all(), null);
           
            if(!$validator->fails()){
                $piso = PisosModel::find($request->id);
                if($piso){
                    $actualizado = $piso->update([
                        "piso"          => $request->piso,
                        'id_sucursal'   => $request->id_sucursal,                                      
                        "id_usuario"    => $request->id_usuario
                    ]);

                    if($actualizado){
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $piso;
                    }
                }else{
                    $this->message  = 'No se encontró el registro que desea actualizar.';
                    $this->status   = 200;
                    $this->error    = true;
                    $this->data     = [];
                }
               
            }else{
                $this->message = $validator->errors();
                $this->status  = 200;
                $this->error = true;
                $this->data    = [];
            }

        }catch(ValidationException $e){
            $this->message = $e->errors();
            $this->status  = 400;
            $this->error = true;
            $this->data    = [];
        }catch (\Throwable $e) {
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status  = 500;
            $this->error = true;
            $this->data    = [];
        }
        return $this->respond();        
    }

    public function editarActivo(Request $request){
        try{
            
            $piso = PisosModel::find($request->id);

            log_actividades('pisos', 'Editar estado', $request, $request->all(), 'Esta es la descripción');
            if($piso){
                $actualizado = $piso->update([
                    "activo" => !$piso->activo,
                ]);

                if($actualizado){
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $piso;
                }
            }else{
                $this->message  = 'No se encontró el registro que desea actualizar.';
                $this->status   = 400;
                $this->error    = true;
                $this->data     = [];
            }
               
        }catch(ValidationException $e){
            $this->message = $e->errors();
            $this->status  = 400;
            $this->error = true;
            $this->data    = [];
        }catch (\Throwable $e) {
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status  = 500;
            $this->error = true;
            $this->data    = [];
        }
        return $this->respond();        
    }
}
