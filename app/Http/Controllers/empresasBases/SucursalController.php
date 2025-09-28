<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\SucursalRequest;
use App\Models\empresasBases\SucursalModel;
use App\Models\empresasBases\UsuarioSucursalModel;
use App\Models\empresasBases\UsusarioSucursalModel;
use App\Traits\ValidaSucursalTrait;

class SucursalController extends respuestaController
{
    use ValidaSucursalTrait;
    public function obtenerRegistros(){
        $sucursales = SucursalModel::select(
        'id', 'sucursal', 'direccion', 'email', 'id_usuario', 'id_sucursal',
        'id_ciudad', 'created_at', 'activo')
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal',
        ])
        ->get();

        foreach ($sucursales as $sucursal) {
            $ciudad = \App\Models\infoclic\CiudadModel::with('departamento:id,departamento')
                ->select('id', 'ciudad', 'id_dep')
                ->find($sucursal->id_ciudad);

            $sucursal->ciudad = $ciudad;
        }


        if($sucursales->isEmpty()){
            $this->message = 'No se encontraron sucursales registradas en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $sucursales; 
        return $this->respond();
    }

    public function obtenerUsuariosSucursales(Request $request){
       // 1. Todas las sucursales
        $todasSucursales = SucursalModel::all();

        // 2. Sucursales relacionadas al usuario
        $sucursalesUsuario = UsuarioSucursalModel::where('id_usuario', $request->id)
            ->pluck('id_sucursal')
            ->toArray();

         // 3. Marcar cuáles están asignadas
        $resultado = $todasSucursales->map(function($sucursal) use ($sucursalesUsuario) {
            return [
                'id' => $sucursal->id,
                'activo' => $sucursal->activo,
                'sucursal' => $sucursal->sucursal, // ajusta al nombre de tu campo
                'asignada' => in_array($sucursal->id, $sucursalesUsuario),
            ];
        });

        $this->status  = 200;
        $this->error = false;
        $this->data    = $resultado; 
        return $this->respond();
    }

    public function crearRegistro(Request $request){
        $validator = $this->validarDatosSucursal($request->all());
        if ($validator->fails()) {
            $this->message = $validator->errors();
            $this->status  = 200;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }

        try {
            $sucursal = SucursalModel::create([
                'sucursal'      => $request->sucursal,
                'direccion'     => $request->direccion,
                'tels'          => $request->tels,
                'email'         => $request->email,
                'id_ciudad'     => $request->id_ciudad,
                'activo'        => 0,
                'id_sucursal'   => $request->id_sucursal,
                'id_usuario'    => $request->id_usuario,
            ]);

            $this->message = 'El registro fue creado correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $sucursal;

        } catch (\Throwable $e) {
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
        }
        return $this->respond(); 
    }

    public function usuariosSucursales(Request $request){
       
        try {
            $consulta = UsuarioSucursalModel::select('id', 'id_usuario', 'id_sucursal')
            ->where('id_usuario', $request->id_usuario)
            ->where('id_sucursal', $request->id_sucursal)->first();

            if($consulta){
                if($request->estado == true){
                    $consulta = UsuarioSucursalModel::update([
                        'id_sucursal'   => $request->id_sucursal,
                        'id_usuario'    => $request->id_usuario
                    ]);
                     $this->message = 'El registro fue editado correctamente.';
                }else{
                    $consulta->delete();
                     $this->message = 'El registro fue eliminado correctamente.';
                }
                
                if($consulta){
                    $this->status  = 200;
                    $this->error   = false;
                    $this->data    = $consulta;
                }else{
                    $this->message = 'Se presentó un error al editar el registro.';
                    $this->status  = 200;
                    $this->error   = true;
                    $this->data    = [];
                }
            }else{
                if($request->estado == true){
                    $sucursal = UsuarioSucursalModel::create([
                        'id_sucursal'   => $request->id_sucursal,
                        'id_usuario'    => $request->id_usuario
                    ]);
               
                    if($sucursal){
                        $this->message = 'El registro fue creado correctamente.';
                        $this->status  = 200;
                        $this->error   = false;
                        $this->data    = $sucursal;
                    }else{
                        $this->message = 'Se presentó un error al crear el registro.';
                        $this->status  = 200;
                        $this->error   = true;
                        $this->data    = [];
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
        }
        return $this->respond(); 
    }

    public function editarRegistro(Request $request, SucursalRequest $validaSucursal){
        try{
            $validator = $this->validarDatosSucursal($request->all());

            if(!$validator->fails()){
                $sucursal = SucursalModel::find($request->id);
                if($sucursal){
                    log_actividades('sucursales', 'Editar', $request, $request->all(), null);
                    $actualizado = $sucursal->update([
                        "sucursal"      => $request->sucursal,
                        "direccion"     => $request->direccion,
                        "tels"          => $request->tels,
                        "email"         => $request->email,      
                        "id_ciudad"     => $request->id_ciudad,
                        'id_sucursal'   => $request->id_sucursal,                                      
                        "id_usuario"    => $request->id_usuario
                    ]);

                    if($actualizado){
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $sucursal;
                    }
                }else{
                    $this->message  = 'No se encontró el registro que desea actualizar.';
                    $this->status   = 400;
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
            
            $sucursal = SucursalModel::find($request->id);

            log_actividades('sucursales', 'Editar estado', $request, $request->all(), 'Esta es la descripción');
            if($sucursal){
                $actualizado = $sucursal->update([
                    "activo" => !$sucursal->activo,
                ]);

                if($actualizado){
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $sucursal;
                }
            }else{
                $this->message  = 'No se encontró el registro que desea actualizar.';
                $this->status   = 200;
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
