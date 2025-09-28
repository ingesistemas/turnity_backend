<?php

namespace App\Http\Controllers\empresasBases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\UsuarioRequest;
use App\Models\empresasBases\UsuarioModel;
use App\Models\empresasBases\RoleModel;
use App\Models\empresasBases\SucursalModel;
use App\Models\empresasBases\UsuarioRoleModel;
use Illuminate\Support\Facades\Hash;
use App\Traits\ValidaUsuarioTrait;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\ValidaEditaClaveTrait;
use App\Traits\ValidarEditarClaveTrait;

class UsuarioController extends respuestaController
{
    use ValidaUsuarioTrait;
    use ValidarEditarClaveTrait;
    public function obtenerRegistros(Request $request){
        try {
            $usuarios = UsuarioModel::select('id', 'nombre', 'email', 'celular', 'activo', 'id_usuario', 'id_profesion', 'created_at')
            ->with([
                'roles:id,rol',
                'sucursales:id,sucursal',
                'profesion:id,profesion',
                'creador:id,nombre,email,celular'
            ])
            ->get();
            

            $this->message = 'Usuarios obtenidos correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $usuarios;

        } catch (\Throwable $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
        }
        return $this->respond();
    }

    public function obtenerRegistrosSucursal(Request $request){
        try {
            $usuarios = UsuarioModel::select('id', 'nombre', 'email', 'celular', 'activo', 'id_usuario', 'id_profesion', 'created_at')
            ->with([
                'roles:id,rol',
                'sucursales:id,sucursal',
                'profesion:id,profesion',
                'creador:id,nombre,email,celular'
            ])->where('id_sucursal', $request->id_sucursal)
            ->get();
            

            $this->message = 'Usuarios obtenidos correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $usuarios;

        } catch (\Throwable $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
        }
        return $this->respond();
    }

    public function obtenerRegistroProfesion(Request $request){
        try {
            $usuarios = UsuarioModel::select('id', 'nombre', 'email', 'celular', 'activo', 'id_usuario', 'id_profesion', 'created_at')
            ->with([
                'roles:id,rol',
                'sucursales:id,sucursal',
                'profesion:id,profesion',
                'creador:id,nombre,email,celular'
            ])->where('id_profesion', $request->id_profesion)
            ->get();
            

            $this->message = 'Usuarios obtenidos correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $usuarios;

        } catch (\Throwable $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
        }
        return $this->respond();
    }

    public function obtenerEmail(Request $request){
        try {
            $usuario = UsuarioModel::select('id', 'email')
            ->where('email', $request->email)
            ->get();
            
            $this->message = 'Usuarios obtenidos correctamente.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = $usuario;

        } catch (\Throwable $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
        }
        return $this->respond();
    }

    public function crearRegistro(Request $request){
        $usuario = false;
        try{
            $validator = $this->validarDatosUsuario($request->all());
            //$rol = RoleModel::find($request->id_rol);
            //$sucursal = RoleModel::find($request->id_sucursal);
            
            $password = Hash::make('admin');

            if ($validator->fails()) {
                $this->message = $validator->errors();
                $this->status  = 200;
                $this->error   = true;
                $this->data    = [];
                return $this->respond();
            }

            $usuario = UsuarioModel::create([
                "nombre" => $request->nombre,
                "usuario" => $request->email,
                "password" => $password,
                "celular"       => $request->celular,
                "email"         => $request->email,
                "id_profesion"  => $request->id_profesion,
                "id_sucursal"   => $request->id_sucursal,
                "activo"        => 0,
                "id_usuario"    => $request->id_usuario
            ]);
            if($usuario){
                $this->message = 'El registro fue creado correctamente.';
                $this->status  = 200;
                $this->error = false;
                $this->data    = $usuario;   
            }else{
                $this->message = 'Se presentó un error al crear el registro.';
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

    public function editarRegistro(Request $request){
        try{
            $validator = $this->validarDatosUsuario($request->all(), true, $request->id);
            
            if(!$validator->fails()){
                $usuario = UsuarioModel::find($request->id);
                if($usuario){  
                    $actualizado = $usuario->update([
                        "nombre" => $request->nombre,
                        //"usuario" => $request->usuario,
                        //"password" => $request->password,
                        "celular" => $request->celular,
                        "email" => $request->email,
                        "id_profesion" => $request->id_profesion,
                        "id_sucursal" => $request->id_sucursal,
                        "id_usuario" => $request->id_usuario
                    ]);

                    if($actualizado){
                        log_actividades('usuarios', 'Editar', $request, $request->all(), null);
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $usuario;
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

    public function editarRol(Request $request){
         
        try{
            //$actualizado = false;
            
            $usuario_rol = UsuarioRoleModel::find($request->id);
            if($usuario_rol){
                $actualizado = $usuario_rol->update([
                    "id_rol" => $request->id_rol,
                ]);
                if($actualizado){
                    log_actividades('usuarios', 'Editar rol', $request, $request->all(), null);
                    $this->message  = 'El rol fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $actualizado;
                }else{
                    $this->message  = 'Se prresentó un error al editar la asignación el rol.';
                    $this->status   = 200;
                    $this->error    = true;
                    $this->data     = [];
                }
            }else{
                $rol_nuevo = UsuarioRoleModel::create([
                    "id_usuario" => $request->id_usuario,
                    "id_rol" => 2
                ]);
                if($rol_nuevo){
                    log_actividades('usuarios', 'Editar rol', $request, $request->all(), null);
                    $this->message  = 'El rol fue creado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $rol_nuevo;
                }else{
                    $this->message  = 'Se prresentó un error al asignar el rol.';
                    $this->status   = 200;
                    $this->error    = true;
                    $this->data     = [];
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
        $this->data =  $usuario_rol;
        return $this->respond();        
    }

    public function editarclave(Request $request){
        try{
            $validator = $this->validarDatosclave($request->all(), true, $request->id);
            
            if(!$validator->fails()){
                $usuario = UsuarioModel::where('email', $request->email)->first();
                if($usuario){  
                    $password = Hash::make($request->password);
                    $actualizado = $usuario->update([
                        "usuario" => $request->usuario,
                        "password" => $password,
                        "id_sucursal" => $request->id_sucursal,
                        "id_usuario" => $request->id_usuario
                    ]);

                    if($actualizado){
                        log_actividades('usuarios', 'Editar', $request, $request->all(), null);
                        $this->message  = 'El registro fue editado correctamente.';
                        $this->status   = 200;
                        $this->error    = false;
                        $this->data     = $usuario;
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
                       
            $usuario = UsuarioModel::find($request->id);
            if($usuario){
                $actualizado = $usuario->update([
                    "activo" => !$usuario->activo,
                ]);

                if($actualizado){
                    log_actividades('usuarios', 'Editar estado', $request, $request->all(), null);
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $usuario;
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

    public function login(Request $request)
    {
        try {
            $usuario = UsuarioModel::select('id', 'usuario', 'password', 'nombre', 'email', 'activo')
            ->where('usuario', $request->usuario)
            ->where('activo', 0)
            ->with([
                'roles' => fn($query) => $query->select('roles.id', 'rol', 'descripcion')  // <- está bien
            ])->first();

            if ($usuario) {
                
                $usuario->load([
                    'sucursales' => fn($q) => $q->where('sucursales.id', $request->id_sucursal)
                    ->select('sucursales.id', 'sucursal', 'direccion')
                ]);
            }

            if (!$usuario || !Hash::check($request->password, $usuario->password)) {
                $this->message = 'Usuario inactivo o credenciales incorrectas. Verifique con soporte.';
                $this->status  = 200;
                $this->error   = true;
                $this->data    = [];
                return $this->respond();
            }

            $token = JWTAuth::fromUser($usuario);

            unset($usuario->password, $usuario->usuario);

            $this->message = 'Autenticación exitosa.';
            $this->status  = 200;
            $this->error   = false;
            $this->data    = [$usuario];
            $this->token = $token;
            return $this->respond();

        } catch (\Throwable $e) {
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }
    }

    public function sucursalesUsuarios(Request $request)
    {
        try {
            $usuario = UsuarioModel::where('usuario', $request->usuario)->first();

            if (!$usuario || !Hash::check($request->password, $usuario->password)) {
                $this->message = 'Credenciales inválidas.';
                $this->status  = 200;
                $this->error   = true;
                $this->data    = [];
                return $this->respond();
            }

            // Obtener únicamente las sucursales relacionadas con ese usuario
            $sucursales = $usuario->sucursales()
                ->select('sucursales.id', 'sucursales.sucursal', 'sucursales.direccion')
                ->get();
        

            if ($sucursales->isEmpty()) {
                $this->message = 'No se encontraron sucursales asociadas a este usuario.';
                $this->status  = 200;
                $this->error   = true;
                $this->data    = [];
            } else {
                $this->message = 'Sucursales obtenidas correctamente.';
                $this->status  = 200;
                $this->error   = false;
                $this->data    = $sucursales;
            }

            return $this->respond();

        } catch (\Throwable $e) {
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }
    }

}
