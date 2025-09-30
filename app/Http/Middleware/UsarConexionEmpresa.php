<?php

namespace App\Http\Middleware;

use App\Http\Controllers\respuestaController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;
use App\Models\infoclic\empresaInfoclicModel;

class UsarConexionEmpresa extends respuestaController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        try {
            $nit = $request->header('X-Empresa-NIT');
            $aplicacionId = $request->header('X-Aplicacion-ID');

            // Intentar consultar la empresa
            $empresa = DB::table('empresas_infoclic')
                ->join('empresas_aplicaciones', 'empresas_infoclic.id', '=', 'empresas_aplicaciones.id_empresa')
                ->join('aplicaciones', 'aplicaciones.id', '=', 'empresas_aplicaciones.id_aplicacion')
                ->where('empresas_infoclic.nit', $nit)
                ->where('aplicaciones.id', $aplicacionId)
                ->select('empresas_infoclic.nit', 'aplicaciones.aplicacion', 'empresas_infoclic.usuario', 'empresas_infoclic.clave')
                ->first();

            if (!$empresa) {
                $this->message = 'No se encontró empresa o aplicación válida para el NIT: ' . $nit;
                $this->status  = 200;
                $this->error   = false;
                $this->data    = [];
                return $this->respond();
            }

            $usuario = $empresa->usuario;
            $clave = $empresa->clave;

            // Base de datos principal de empresa
            Config::set('database.connections.infoclic', [
                'driver' => 'mysql',
                //'host' => env('DB_HOST', '127.0.0.1'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => 'infoclic',
                'username' => 'infoclic',
                'password' => 'Minelye_74',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            // Base de datos por aplicación
            Config::set('database.connections.multi_empresa', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => strtolower($empresa->aplicacion . $empresa->nit),
                'username' => $usuario,
                'password' => $clave,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            // Base de datos principal de empresa
            Config::set('database.connections.empresa_base', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => 'bd' . $empresa->nit,
                'username' => $usuario,
                'password' => $clave,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            DB::purge('multi_empresa');
            DB::purge('empresa_base');

            DB::reconnect('multi_empresa');
            DB::reconnect('empresa_base');

            return $next($request);

        } catch (\Throwable $e) {
            $this->message = 'Error inesperado en middleware de conexión. Comunícate con un Asesor Infoclic. ' ;
            $this->status  = 500;
            $this->error   = true;
            $this->data    = [];
            return $this->respond();
        }
    }

}
