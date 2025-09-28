<?php

namespace App\Http\Controllers\turnity;

use App\Http\Controllers\respuestaController;
use App\Models\empresasBases\ClienteModel;
use App\Models\turnity\AsignacionesModel;
use App\Models\turnity\TurnosModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ValidaTurnosTrait;

class TurnosPantallaController extends respuestaController
{
    use ValidaTurnosTrait;
    public function crearRegistroPantalla(Request $request)  //ASIGNAR TURNOS
    {
        $validator = $this->validarDatosTurnosPantalla($request->all());

        if ($validator->fails()) {
            $this->message = $validator->errors();
            $this->status  = 200;
            $this->error   = true;
            $this->data    = $validator->errors();
            return $this->respond();
        }

        try {
            $data = DB::connection('multi_empresa')->transaction(function () use ($request) {
                
                $horaActual = Carbon::now()->toTimeString();
                $fechaActual = Carbon::now()->toDateString();
                $idPaciente = $request->id_paciente;

                if($request->id_paciente == 0){
                    $paciente = ClienteModel::create([
                        'documento'     => $request->documento,
                        'nombre'        => $request->nombre,
                        'id_sucursal'       => $request->id_sucursal,
                        'id_usuario'        => $request->id_usuario
                    ]);

                    $idPaciente = $paciente->id;
                }

                $valida = TurnosModel::where('id_paciente', $request->id_paciente)
                                        ->where('hora_fin', '00:00:00')->get();
                if($valida->isEmpty()){
                    $turno = TurnosModel::create([
                        'fecha'             => $fechaActual,
                        'id_paciente'       => $idPaciente,
                        'hora_llegada'      => $horaActual,
                        'hora_ini'          => '00:00:00',
                        'hora_fin'          => '00:00:00',
                        'hora_asignacion'   => '00:00:00',
                        'activo'            => 0,
                        'id_caso_turno'     => 6,
                        'id_prioritaria'    => 11,
                        'id_sucursal'       => $request->id_sucursal,
                        'id_usuario'        => $request->id_usuario,
                    ]);

                    // Simula error: este campo no existe => lanzará excepción
                    $asignacion = AsignacionesModel::create([
                        'id_turno'      => $turno->id, // usa la clave primaria correcta
                        'id_operario'   => 0,
                        'fecha'         => $fechaActual,
                        'hora_asigna'   => '00:00:00',
                        'hora_ini'      => '00:00:00',
                        'hora_fin'      => '00:00:00',
                        'hora_ope_ini'  => $request->hor_ope_ini,
                        'hora_ope_fin'  => $horaActual,
                        'id_caso'       => 6,
                        'id_usuario'    => $request->id_usuario,
                        'id_sala'       => $request->id_sala
                    ]);

                    $this->message = 'El turno fue asignado con éxito.';
                    $this->status = 200;
                    $this->error = false;
                    $this->data = [$turno, $asignacion];
                }else{
                    $this->message = 'Este paciente ya tiene un turno asignado; por tanto, como primera medida, se debe dar solución a la atención pendiente.';
                    $this->status = 200;
                    $this->error = true;
                    $this->data = [];
                }
            });

        } catch (\Throwable $e) {
            DB::rollback();
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status = 500;
            $this->error = true;
            $this->data = [];
        }

        return $this->respond();
    }

    public function listarTurnosSalas(Request $request){ 

        $turnos = TurnosModel::with([
            'asignaciones' => function ($q) {
                $q->where('id_operario', 0)
                ->where(function ($q) {
                    $q->where('hora_fin', '00:00:00')
                        ->orWhereNull('hora_fin');
                })
                ->with([
                    'operario' => function ($q) {
                        $q->select('id', 'nombre', 'celular', 'email', 'id_profesion')
                            ->with([
                                'profesion' => function ($q) {
                                    $q->select('id', 'profesion');
                                }
                            ]);
                    },
                    'usuarios' => function ($q) {
                        $q->select('id', 'nombre', 'celular', 'email');
                    },
                    'caso',
                    'sala' => function ($q) {
                        $q->with([
                            'piso' => function ($q) {
                                $q->select('id', 'piso');
                            }
                        ]);
                    }
                ]);
            },
            'prioritaria' => function ($q) {
                $q->select('id', 'prioritaria');
            },
            'paciente' => function ($q) {
                $q->select('id', 'documento', 'nombre');
            }
        ])
        ->where('id_sucursal', $request->id_sucursal)
        ->whereHas('asignaciones', function ($q) {
            $q->where('id_operario', 0)
            ->where(function ($q) {
                $q->where('hora_fin', '00:00:00')
                    ->orWhereNull('hora_fin');
            });
        })
        ->get();

        if($turnos->isEmpty()){
            $this->message = 'Actualmente no tienes turnos asignados.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $turnos; 
        return $this->respond(); 
    }
}