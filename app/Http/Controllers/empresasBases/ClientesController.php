<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\SucursalRequest;
use App\Models\empresasBases\ClienteModel;
use App\Traits\ValidaClientesTrait;

class ClientesController extends respuestaController
{
    use ValidaClientesTrait;
    public function obtenerRegistros(){
        $clientes = ClienteModel::select(
            'id', 'documento', 'nombre', 'id_usuario', 'id_sucursal',
            'created_at', 'activo'
        )
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal'
        ])->get();

        if($clientes->isEmpty()){
            $this->message = 'No se encontraron clientes registrados en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $clientes; 
        return $this->respond();
    }

    public function obtenerRegistro(Request $request){
        $clientes = ClienteModel::select(
            'id', 'documento', 'nombre', 'id_usuario', 'id_sucursal',
            'created_at'
        )
        ->with([
            'usuarios:id,nombre,email',
            'sucursalPadre:id,sucursal'
        ])
        ->where('documento', $request->documento)
        ->get();

        if($clientes->isEmpty()){
            $this->message = 'No se encontraron clientes registrados en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $clientes; 
        return $this->respond();
    }

    public function crearRegistro(Request $request){
       
        $validator = $this->validarDatosClientes($request->all(), false);
        if ($validator->fails()) {
            $this->message = $validator->errors();
            $this->status  = 200;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }

        try {
            $cliente = ClienteModel::create([
                'documento'     => $request->documento,
                'cliente'       => $request->cliente,
                'activo'        => 0,
                'id_sucursal'   => $request->id_sucursal,
                'id_usuario'    => $request->id_usuario,
            ]);

            $this->message = 'El registro fue creado correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $cliente;

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
            $validator = $this->validarDatosClientes($request->all(), true, $request->id_sucursal);

            if(!$validator->fails()){
                log_actividades('clientes', 'Editar', $request, $request->all(), null);
                $cliente = ClienteModel::find($request->id);
                if($cliente){
                    $actualizado = $cliente->update([
                        "documento"     => $request->documento,
                        "nombre"        => $request->nombre,    
                        "id_sucursal"   => $request->id_sucursal,                                      
                        "id_usuario"    => $request->id_usuario
                    ]);

                    if($actualizado){
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $cliente;
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
            $cliente = ClienteModel::find($request->id);
            log_actividades('clientes', 'Editar estado', $request, $request->all(), null);
            if($cliente){
                $actualizado = $cliente->update([
                    "activo" => !$cliente->activo,
                ]);

                if($actualizado){
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $cliente;
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
