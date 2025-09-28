<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class MigrarPorEmpresa extends Command
{
    protected $signature = 'migrar:empresa {nit} {id_aplicacion}';

    protected $description = 'Ejecuta migraciones en la base de datos de una empresa si está asociada a una aplicación.';

    public function handle()
    {
        $nit = $this->argument('nit');
        $aplicacionId = $this->argument('id_aplicacion');

        // Buscar la empresa y su conexión
        $empresa = DB::table('empresas_infoclic')
            ->join('empresas_aplicaciones', 'empresas_infoclic.id', '=', 'empresas_aplicaciones.id_empresa')
            ->join('aplicaciones', 'aplicaciones.id', '=', 'empresas_aplicaciones.id_aplicacion')
            ->where('empresas_infoclic.nit', $nit)
            ->where('aplicaciones.id', $aplicacionId)
            ->select('empresas_infoclic.nit', 'aplicaciones.aplicacion', 'empresas_infoclic.usuario', 'empresas_infoclic.clave')
            ->first();

        if (!$empresa) {
            $this->error("❌ Empresa con NIT {$nit} no encontrada o no asociada a la aplicación ID {$aplicacionId}.");
            return;
        }

        $bd = $empresa->aplicacion . $empresa->nit;
        $usuario = $empresa->usuario;
        $clave = $empresa->clave;

        // Verificar si la base de datos ya existe
        $bdExiste = DB::select("SHOW DATABASES LIKE '$bd'");
        if (!$bdExiste) {
            $this->warn("⚠️ La base de datos '$bd' no existe.");
            if ($this->confirm('¿Deseas crearla ahora?')) {
                DB::statement("CREATE DATABASE `$bd` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $this->info("✅ Base de datos '$bd' creada.");
            } else {
                $this->error("❌ Cancelado. No se puede continuar sin base de datos.");
                return;
            }
        }

        // Configurar conexión dinámica
        Config::set('database.connections.multi_empresa', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $bd,
            'username' => $usuario,
            'password' => $clave,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        DB::purge('multi_empresa'); // limpia conexiones previas
        DB::reconnect('multi_empresa');

        // Verificar si ya hay migraciones ejecutadas
        if (Schema::connection('multi_empresa')->hasTable('migrations')) {
            $this->warn("⚠️ Ya existen migraciones en esta base de datos.");
            if (! $this->confirm('¿Deseas ejecutar migraciones de todos modos?')) {
                $this->info('🚫 Operación cancelada.');
                return;
            }
        }

        // Ejecutar migraciones desde carpeta personalizada
        $this->info("🚀 Ejecutando migraciones para '$bd' (Aplicación ID: $aplicacionId)...");

        Artisan::call('migrate', [
            '--database' => 'multi_empresa',
            '--path' => "database/migrations/{$nit}", // ruta dinámica basada en NIT
            '--force' => true,
        ]);

        $this->info(Artisan::output());
    }
}
