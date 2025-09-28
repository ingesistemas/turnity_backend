<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\SucursalRequest;
use App\Models\empresasBases\PisosModel;
use App\Models\empresasBases\SalasModel;
use App\Models\empresasBases\SucursalModel;
use App\Traits\ValidaSalasTrait;

class SalasController extends respuestaController
{
    use ValidaSalasTrait;
    public function obtenerRegistros(){
        $salas = SalasModel::select(
        'id', 'sala', 'atencion_inicial', 'id_usuario', 'id_sucursal',
        'id_piso', 'created_at', 'activo'
        )
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal',
            'piso:id,piso' // Ajusta campos según tu modelo Piso
        ])->get();

        if($salas->isEmpty()){
            $this->message = 'No se encontraron salas registradas en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $salas; 
        return $this->respond();
    }

    public function obtenerRegistrosSucursal(Request $request){
        $salas = SalasModel::select(
        'id', 'sala', 'atencion_inicial', 'id_usuario', 'id_sucursal',
        'id_piso', 'created_at', 'activo'
        )
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal',
            'piso:id,piso' // Ajusta campos según tu modelo Piso
        ])->where('id_sucursal', $request->id_sucursal)
        ->get();

        if($salas->isEmpty()){
            $this->message = 'No se encontraron salas registradas en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $salas; 
        return $this->respond();
    }


    public function crearRegistro(Request $request){
       
        $validator = $this->validarDatosSalas($request->all(), false);
        if ($validator->fails()) {
            $this->message = $validator->errors();
            $this->status  = 200;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }

        try {
            $sala = SalasModel::create([
                'sala'      => $request->sala,
                'id_piso'     => $request->id_piso,
                'atencion_inicial' => $request->atencion_inicial,
                'activo'        => 0,
                'id_sucursal'   => $request->id_sucursal,
                'id_usuario'    => $request->id_usuario,
            ]);

            $this->message = 'El registro fue creado correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $sala;

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
            $validator = $this->validarDatosSalas($request->all(), true, $request->id);

            if(!$validator->fails()){
                log_actividades('salas', 'Editar', $request, $request->all(), null);
                $sala = SalasModel::find($request->id);
                if($sala){
                    $actualizado = $sala->update([
                        "sala"      => $request->sala,
                        "id_piso"     => $request->id_piso,
                        'atencion_inicial' => $request->atencion_inicial,
                        'id_sucursal'   => $request->id_sucursal,                                      
                        "id_usuario"    => $request->id_usuario
                    ]);

                    if($actualizado){
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $sala;
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
            $sala = SalasModel::find($request->id);
            log_actividades('salas', 'Editar estado', $request, $request->all(), null);
            if($sala){
                $actualizado = $sala->update([
                    "activo" => !$sala->activo,
                ]);

                if($actualizado){
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $sala;
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
