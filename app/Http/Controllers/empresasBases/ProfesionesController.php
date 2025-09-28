<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Models\empresasBases\ProfesionModel;
use App\Traits\ValidaProfesionesTrait;

class ProfesionesController extends respuestaController
{
    use ValidaProfesionesTrait;
    public function obtenerRegistros(){
        $profesiones = ProfesionModel::select(
        'id', 'profesion', 'id_usuario', 'id_sucursal', 'created_at', 'activo'
        )
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal'
        ])->get();

        if($profesiones->isEmpty()){
            $this->message = 'No se encontraron profesiones registradas en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $profesiones; 
        return $this->respond();
    }

    public function crearRegistro(Request $request){
       
        $validator = $this->validarDatosProfesiones($request->all());
        if ($validator->fails()) {
            $this->message = $validator->errors();
            $this->status  = 200;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }

        try {
            $profesion = ProfesionModel::create([
                'profesion'      => $request->profesion,
                'activo'        => 0,
                'id_sucursal'   => $request->id_sucursal,
                'id_usuario'    => $request->id_usuario,
            ]);

            $this->message = 'El registro fue creado correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $profesion;

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
            $validator = $this->validarDatosProfesiones($request->all(), true, $request->id);

            if(!$validator->fails()){
                log_actividades('profesiones', 'Editar', $request, $request->all(), null);
                $profesion = ProfesionModel::find($request->id);
                if($profesion){
                    $actualizado = $profesion->update([
                        "profesion"      => $request->profesion,
                        'id_sucursal'   => $request->id_sucursal,                                      
                        "id_usuario"    => $request->id_usuario
                    ]);

                    if($actualizado){
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $profesion;
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
            $profesion = ProfesionModel::find($request->id);
            log_actividades('profesiones', 'Editar estado', $request, $request->all(), null);
            if($profesion){
                $actualizado = $profesion->update([
                    "activo" => !$profesion->activo,
                ]);

                if($actualizado){
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $profesion;
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
