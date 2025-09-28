<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\RoleRequest;
use App\Models\empresasBases\RoleModel;

class RoleController extends respuestaController
{
    public function obtenerRegistros(){
        $roles = RoleModel::select('id', 'rol', 'id_usuario', 'activo')->get();

        if($roles->isEmpty()){
            $this->message = 'No se encontraron roles registrados en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $roles; 
        return $this->respond();
    }

    public function crearRegistro(Request $request, RoleRequest $validaRol){
        try{
            $valido = $validaRol->crearEditar($request);
            if($valido){
                $rol = RoleModel::create([
                    "rol" => $request->rol,
                    "descripcion" => $request->descripcion,
                    "activo" => 0,
                    "id_usuario" => $request->id_usuario
                ]);

                if($rol){
                    $this->message = 'El registro fue creado correctamente.';
                    $this->status  = 200;
                    $this->error = false;
                    $this->data    = $rol;
                }else{
                    $this->message = 'Se presentó un error al crear el registro.';
                    $this->status  = 400;
                    $this->error = true;
                    $this->data    = [];
                }
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

    public function editarRegistro(Request $request, RoleRequest $validaRol){
        try{
            $valido = $validaRol->crearEditar($request);
           
            if($valido){
                $rol = RoleModel::find($request->id);
                if($rol){
                    $actualizado = $rol->update([
                        "rol" => $request->rol,
                        "descripcion" => $request->descripcion,
                    ]);

                    if($actualizado){
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $rol;
                    }
                }else{
                    $this->message  = 'No se encontró el registro que desea actualizar.';
                    $this->status   = 400;
                    $this->error    = true;
                    $this->data     = [];
                }
               
            }else{
                $this->message = 'Se presentó un error al editar el registro.';
                $this->status  = 400;
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
                       
            $rol = RoleModel::find($request->id);
            if($rol){
                $actualizado = $rol->update([
                    "activo" => !$rol->activo,
                ]);

                if($actualizado){
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $rol;
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
