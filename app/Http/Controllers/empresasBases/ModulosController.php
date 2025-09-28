<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Models\empresasBases\ModuloModel;
use App\Traits\ValidaModulosTrait;

class ModulosController extends respuestaController
{
    use ValidaModulosTrait;
    public function obtenerRegistros(){
        $modulos = ModuloModel::select(
            'id', 'modulo', 'id_usuario', 'id_sucursal',
            'id_sala', 'created_at', 'activo'
        )
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal',
            'sala:id,sala,id_piso',
            'sala.piso:id,piso'
        ])->get();

        if($modulos->isEmpty()){
            $this->message = 'No se encontraron módulos registrados en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $modulos; 
        return $this->respond();
    }

    public function obtenerRegistrosSucursal(Request $request){
        $modulos = ModuloModel::select(
            'id', 'modulo', 'id_usuario', 'id_sucursal',
            'id_sala', 'created_at', 'activo'
        )
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal',
            'sala:id,sala,id_piso',
            'sala.piso:id,piso'
        ])
        ->where('id_sucursal', $request->id_sucursal)
        ->get();

        if($modulos->isEmpty()){
            $this->message = 'No se encontraron módulos registrados en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $modulos; 
        return $this->respond();
    }

    public function crearRegistro(Request $request){
       
        $validator = $this->validarDatosModulos($request->all(), false);
        if ($validator->fails()) {
            $this->message = $validator->errors();
            $this->status  = 200;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }

        try {
            $modulo = ModuloModel::create([
                'modulo'      => $request->modulo,
                'id_sala'     => $request->id_sala,
                'activo'        => 0,
                'id_sucursal'   => $request->id_sucursal,
                'id_usuario'    => $request->id_usuario,
            ]);

            $this->message = 'El registro fue creado correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $modulo;

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
            $validator = $this->validarDatosModulos($request->all(), true, $request->id_sucursal);

            if(!$validator->fails()){
                log_actividades('modulos', 'Editar', $request, $request->all(), null);
                $modulo = ModuloModel::find($request->id);
                if($modulo){
                    $actualizado = $modulo->update([
                        "modulo"      => $request->modulo,
                        "id_sala"     => $request->id_sala,
                        'id_sucursal'   => $request->id_sucursal,                                      
                        "id_usuario"    => $request->id_usuario
                    ]);

                    if($actualizado){
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $modulo;
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
            $modulo = ModuloModel::find($request->id);
            log_actividades('modulos', 'Editar estado', $request, $request->all(), null);
            if($modulo){
                $actualizado = $modulo->update([
                    "activo" => !$modulo->activo,
                ]);

                if($actualizado){
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $modulo;
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
