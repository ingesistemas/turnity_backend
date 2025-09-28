<?php

use App\Http\Controllers\empresasBases\CentrosController;
use App\Http\Controllers\empresasBases\ClientesController;
use App\Http\Controllers\empresasBases\DptosController;
use App\Http\Controllers\empresasBases\ModulosController;
use App\Http\Controllers\empresasBases\PisosController;
use App\Http\Controllers\empresasBases\PrioritariasController;
use App\Http\Controllers\empresasBases\ProfesionesController;
use App\Http\Controllers\empresasBases\SalasController;
use App\Http\Controllers\empresasBases\SucursalController;
use App\Http\Controllers\empresasBases\UsuarioController;
use App\Http\Controllers\infoclic\DptosController as InfoclicDptosController;
use App\Http\Controllers\turnity\CasosController;
use App\Http\Controllers\turnity\TurnosController;
use App\Models\infoclic\CiudadModel;
use Illuminate\Support\Facades\Route;

    use App\Events\llamadoPantalla;
use App\Http\Controllers\empresasBases\RoleController;
use App\Http\Controllers\turnity\TurnosPantallaController;

Route::middleware('api')->group(function () {

    // === BASE DE DATOS PRINCIPAL ===
    


    // EMPRESAS (BD principal)
    /* Route::post('/crear-empresa', [EmpresaController::class, 'crearRegistro']);
    Route::post('/editar-empresa', [EmpresaController::class, 'editarRegistro']);
    Route::post('/activo-empresa', [EmpresaController::class, 'editarActivo']); */

    // === BASE DE DATOS DE CADA EMPRESA ===
    Route::middleware('conexion.empresa')->group(function () {

        // SUCURSALES (BD por empresa)

         //Usuarios
        Route::post('/obtener-usuarios', [UsuarioController::class, 'obtenerRegistros']);
        Route::post('/obtener-usuarios-sucursal', [UsuarioController::class, 'obtenerRegistrosSucursal']);
        Route::post('/obtener-usuario-profesion', [UsuarioController::class, 'obtenerRegistroProfesion']);
        Route::post('/login', [UsuarioController::class, 'login']);
        Route::post('/sucursalesUsuarios', [UsuarioController::class, 'sucursalesUsuarios']);
        Route::post('/crear-usuario', [UsuarioController::class, 'crearRegistro']);
        Route::post('/editar-usuario', [UsuarioController::class, 'editarRegistro']);
        Route::post('/editar-rol', [UsuarioController::class, 'editarRol']);
        Route::post('/activo-usuario', [UsuarioController::class, 'editarActivo']);
        Route::post('/obtener-email', [UsuarioController::class, 'obtenerEmail']);
        Route::post('/editar-clave', [UsuarioController::class, 'editarClave']);


        //Sucursales
        Route::post('/crear-sucursal', [SucursalController::class, 'crearRegistro']);
        Route::post('/editar-sucursal', [SucursalController::class, 'editarRegistro']);
        Route::post('/activo-sucursal', [SucursalController::class, 'editarActivo']);
        Route::post('/obtener-sucursales', [SucursalController::class, 'obtenerRegistros']);
        Route::post('/usuarios-sucursales', [SucursalController::class, 'usuariosSucursales']);
        Route::post('/obtener-usuarios-sucursales', [SucursalController::class, 'obtenerUsuariosSucursales']);


        //Roles
        Route::post('/obtener-roles', [RoleController::class, 'obtenerRegistros']);

        //Pisos
        Route::post('/obtener-pisos', [PisosController::class, 'obtenerRegistros']);
        Route::post('/obtener-pisos-sucursal', [PisosController::class, 'obtenerRegistrosSucursal']);
        Route::post('/crear-piso', [PisosController::class, 'crearRegistro']);
        Route::post('/editar-piso', [PisosController::class, 'editarRegistro']);
        Route::post('/activo-piso', [PisosController::class, 'editarActivo']);

        //Salas
        Route::post('/obtener-salas', [SalasController::class, 'obtenerRegistros']);
        Route::post('/obtener-salas-sucursal', [SalasController::class, 'obtenerRegistrosSucursal']);
        Route::post('/crear-sala', [SalasController::class, 'crearRegistro']);
        Route::post('/editar-sala', [SalasController::class, 'editarRegistro']);
        Route::post('/activo-sala', [SalasController::class, 'editarActivo']);

        //Centros de atención
        Route::post('/obtener-centros', [CentrosController::class, 'obtenerRegistros']);
        Route::post('/crear-centro', [CentrosController::class, 'crearRegistro']);
        Route::post('/editar-centro', [CentrosController::class, 'editarRegistro']);
        Route::post('/activo-centro', [CentrosController::class, 'editarActivo']);

        //Módulos de atención
        Route::post('/obtener-modulos', [ModulosController::class, 'obtenerRegistros']);
        Route::post('/obtener-modulos-sucursal', [ModulosController::class, 'obtenerRegistrosSucursal']);
        Route::post('/crear-modulo', [ModulosController::class, 'crearRegistro']);
        Route::post('/editar-modulo', [ModulosController::class, 'editarRegistro']);
        Route::post('/activo-modulo', [ModulosController::class, 'editarActivo']);

        //Profesiones
        Route::post('/obtener-profesiones', [ProfesionesController::class, 'obtenerRegistros']);
        Route::post('/crear-profesion', [ProfesionesController::class, 'crearRegistro']);
        Route::post('/editar-profesion', [ProfesionesController::class, 'editarRegistro']);
        Route::post('/activo-profesion', [ProfesionesController::class, 'editarActivo']);

        //Clientes
        Route::post('/obtener-clientes', [ClientesController::class, 'obtenerRegistros']);
        Route::post('/obtener-cliente', [ClientesController::class, 'obtenerRegistro']);
        Route::post('/crear-cliente', [ClientesController::class, 'crearRegistro']);
        Route::post('/editar-cliente', [ClientesController::class, 'editarRegistro']);
        Route::post('/activo-cliente', [ClientesController::class, 'editarActivo']);

        //Turnos
        Route::post('/crear-turno', [TurnosController::class, 'crearRegistro']);
        Route::post('/editar-turno', [TurnosController::class, 'editarRegistro']);
        Route::post('/obtener-turnos', [TurnosController::class, 'obtenerRegistros']);
        Route::post('/listar-turnos', [TurnosController::class, 'listarTurnos']);
        Route::post('/ultimos-pacientes', [TurnosController::class, 'ultimosPacientes']);
        Route::post('/listar-todos-turnos', [TurnosController::class, 'listarTodosLosTurnos']);
        Route::post('/listar-turnos-diarios', [TurnosController::class, 'listarTurnosDiarios']);
        Route::post('/listar-turnos-fechas', [TurnosController::class, 'listarTurnosFechas']);
        Route::post('/llamado-operario', [TurnosController::class, 'registrarLlamado']);
        Route::post('/hora-inicial-turno', [TurnosController::class, 'horaInicialTurno']);
        Route::post('/res-hora-inicial-turno', [TurnosController::class, 'restablecerHoraInicialTurno']);
        Route::post('/estadisticas-fechas', [TurnosController::class, 'estadisticasTurnos']);
        Route::post('/actualizar-llamado', [TurnosController::class, 'actualizarLlamado']);
        Route::post('/disparar', [TurnosController::class, 'disparar']);
        Route::post('/disparar-seguimiento', [TurnosController::class, 'dispararSeguimiento']);
        
        //Turnos pantalla
        Route::post('/crear-turno-pantalla', [TurnosPantallaController::class, 'crearRegistroPantalla']);
        Route::post('/listar-turnos-salas', [TurnosPantallaController::class, 'listarTurnosSalas']);

        //Prioritarias
        Route::post('/obtener-prioritarias', [PrioritariasController::class, 'obtenerRegistros']);

        //Casos
        Route::post('/obtener-casos', [CasosController::class, 'obtenerRegistros']);
 
    });

    Route::post('/dptos', [InfoclicDptosController::class, 'obtenerDptos']);
    Route::post('/ciudades', [InfoclicDptosController::class, 'obtenerCiudades']);

});