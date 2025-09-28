<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\SucursalRequest;
use App\Models\empresasBases\CentrosModel;
use App\Models\empresasBases\PisosModel;
use App\Models\empresasBases\SalasModel;
use App\Models\empresasBases\SucursalModel;
use App\Traits\ValidaCentrosTrait;

class CentrosController extends respuestaController
{
    use ValidaCentrosTrait;
    public function obtenerRegistros(){
        $centros = CentrosModel::select(
            'id', 'centro', 'id_usuario', 'id_sucursal',
            'created_at', 'activo'
        )
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal'
        ])->get();

        if($centros->isEmpty()){
            $this->message = 'No se encontraron centros registrados en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $centros; 
        return $this->respond();
    }

    public function crearRegistro(Request $request){
       
        $validator = $this->validarDatosCentros($request->all(), false);
        if ($validator->fails()) {
            $this->message = $validator->errors();
            $this->status  = 200;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }

        try {
            $centro = CentrosModel::create([
                'centro'      => $request->centro,
                'activo'        => 0,
                'id_sucursal'   => $request->id_sucursal,
                'id_usuario'    => $request->id_usuario,
            ]);

            $this->message = 'El registro fue creado correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $centro;

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
            $validator = $this->validarDatosCentros($request->all(), true, $request->id_sucursal);

            if(!$validator->fails()){
                log_actividades('centros', 'Editar', $request, $request->all(), null);
                $centro = CentrosModel::find($request->id);
                if($centro){
                    $actualizado = $centro->update([
                        "centro"      => $request->centro,
                        'id_sucursal'   => $request->id_sucursal,                                      
                        "id_usuario"    => $request->id_usuario
                    ]);

                    if($actualizado){
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $centro;
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
            $centro = CentrosModel::find($request->id);
            log_actividades('centros', 'Editar estado', $request, $request->all(), null);
            if($centro){
                $actualizado = $centro->update([
                    "activo" => !$centro->activo,
                ]);

                if($actualizado){
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $centro;
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
