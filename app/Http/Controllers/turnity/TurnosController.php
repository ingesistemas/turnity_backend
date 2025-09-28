<?php

namespace App\Http\Controllers\turnity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\respuestaController;
use App\Http\Requests\SucursalRequest;
use App\Models\empresasBases\CentrosModel;
use App\Models\empresasBases\ClienteModel;
use App\Models\empresasBases\PisosModel;
use App\Models\empresasBases\SalasModel;
use App\Models\empresasBases\SucursalModel;
use App\Models\turnity\AsignacionesModel;
use App\Models\turnity\LlamadosModel;
use App\Models\turnity\TurnosModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Events\llamadoPantalla;
use App\Events\SeguimientoDiario;
use App\Traits\ValidaTurnosTrait;

class TurnosController extends respuestaController
{
    use ValidaTurnosTrait;
    public function obtenerRegistros(){
        $turnos = TurnosModel::with([
        'asignaciones' => function ($q) {
            $q->with([
                // Operario con profesión y campos seleccionados
                'operario' => function ($q) {
                    $q->select('id', 'nombre', 'celular', 'email', 'id_profesion')
                    ->with([
                        'profesion' => function ($q) {
                            $q->select('id', 'profesion');
                        }
                    ]);
                },
                // Usuario que hizo la asignación (id_usuario)
                'usuarios' => function ($q) {
                    $q->select('id', 'nombre', 'celular', 'email');
                },
                // Caso relacionado
                'caso',
                // Sala con piso
                'sala' => function ($q) {
                    $q->with([
                        'piso' => function ($q) {
                            $q->select('id', 'piso');
                        }
                    ]);
                }
            ]);
        },
        // Prioridad del turno
        'prioritaria' => function ($q) {
            $q->select('id', 'prioritaria');
        },
        'paciente' => function ($q) {
            $q->select('id', 'documento', 'nombre');
        }
        ])->get();


        if($turnos->isEmpty()){
            $this->message = 'No se encontraron centros registrados en el sistema.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $turnos; 
        return $this->respond(); 
    }

    public function listarTurnos(Request $request){ // Lista turno por operarios pendientes por llamar
        $usuarioId = $request->id_usuario;

        $turnos = TurnosModel::with([
            'asignaciones' => function ($q) use ($usuarioId) {
                $q->where('id_operario', $usuarioId)
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
        ->whereHas('asignaciones', function ($q) use ($usuarioId) {
            $q->where('id_operario', $usuarioId)
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

    public function listarTodosLosTurnos(Request $request)
    {
        // Lista todos los turnos con asignaciones pendientes por llamar
        $turnos = TurnosModel::with([
            'asignaciones' => function ($q) {
                $q->where(function ($q) {
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
            $q->where('hora_fin', '00:00:00')
                ->orWhereNull('hora_fin');
        })
        ->get();

        if ($turnos->isEmpty()) {
            $this->message = 'Actualmente no hay turnos asignados.';
        } else {
            $this->message = 'Registros obtenidos.';
        }

        $this->status = 200;
        $this->error = false;
        $this->data = $turnos;

        return $this->respond();
    }

    public function listarTurnosDiarios(Request $request, $actualiza = null){  // Consulta de turnos diarios
        $usuarioId = $request->id_usuario;
        $fecha = Carbon::now()->toDateString();

        $turnos = TurnosModel::with([
            'paciente' => function ($q) {
                $q->select('id', 'documento', 'nombre'); // Agrega 'telefono' aquí si lo necesitas en el frontend.
            },
            'prioritaria' => function ($q) {
                $q->select('id', 'prioritaria');
            },
            'destino' => function ($q) {
                $q->select('id', 'caso', 'clase'); // ¡Aquí se obtiene el destino desde CasosModel!
            },
            'asignaciones' => function ($q) use ($usuarioId) {
                // Selecciona los campos necesarios de la tabla 'asignaciones'
                $q->select(
                    'id', 'id_turno', 'fecha',
                    'hora_asigna', 'hora_ini', 'hora_fin',
                    'id_sala', 'id_caso', 'id_operario', 'id_usuario', 'id_modulo', 'created_at' // <-- ¡'id_modulo' es clave aquí!
                )
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
                    'caso' => function ($q) {
                            $q->select('id', 'caso');
                    },
                    'sala' => function ($q) {
                        $q->select('id', 'sala', 'id_piso')
                            ->with([
                                'piso' => function ($q) {
                                    $q->select('id', 'piso');
                                }
                            ]);
                    },
                    // ¡Aquí añadimos la relación 'modulo' para el AsignacionesModel!
                    // El campo en ModuloModel es 'modulo', no 'nombre_del_modulo'
                    'modulo' => function ($q) { 
                        $q->select('id', 'modulo'); // <-- ¡Campo 'modulo' del ModuloModel!
                    },
                    'llamados' => function ($q) {
                        $q->select('id', 'id_asigna', 'hora_llamado', 'id_usuario', 'created_at');
                    }
                ])
                ->orderBy('created_at', 'asc'); // Para ordenar las asignaciones
            }
        ])
        ->where('fecha', $fecha)
        ->where('id_sucursal', $request->id_sucursal) // Acceso directo a $request->id_sucursal
        ->orderBy('hora_llegada', 'asc')
        ->get();

        if($turnos->isEmpty()){
            $this->message = 'No se encontraron turnos asignados en el día de hoy.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        if($actualiza == null){
            $this->status  = 200;
            $this->error = false;
            $this->data    = $turnos; 
            return $this->respond(); 
        }else{
            return $turnos;
        }

    }

    public function listarTurnosFechas(Request $request){  // Consulta de turnos con fecha inicial y final
        $usuarioId = $request->id_usuario;
        $fecha = Carbon::now()->toDateString();

        $turnos = TurnosModel::with([
            'paciente' => function ($q) {
                $q->select('id', 'documento', 'nombre'); // Agrega 'telefono' aquí si lo necesitas en el frontend.
            },
            'prioritaria' => function ($q) {
                $q->select('id', 'prioritaria');
            },
            'destino' => function ($q) {
                $q->select('id', 'caso', 'clase'); // ¡Aquí se obtiene el destino desde CasosModel!
            },
            'asignaciones' => function ($q) use ($usuarioId) {
                // Selecciona los campos necesarios de la tabla 'asignaciones'
                $q->select(
                    'id', 'id_turno', 'fecha',
                    'hora_asigna', 'hora_ini', 'hora_fin',
                    'id_sala', 'id_caso', 'id_operario', 'id_usuario', 'id_modulo', 'created_at' // <-- ¡'id_modulo' es clave aquí!
                )
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
                    'caso' => function ($q) {
                            $q->select('id', 'caso');
                    },
                    'sala' => function ($q) {
                        $q->select('id', 'sala', 'id_piso')
                            ->with([
                                'piso' => function ($q) {
                                    $q->select('id', 'piso');
                                }
                            ]);
                    },
                    // ¡Aquí añadimos la relación 'modulo' para el AsignacionesModel!
                    // El campo en ModuloModel es 'modulo', no 'nombre_del_modulo'
                    'modulo' => function ($q) { 
                        $q->select('id', 'modulo'); // <-- ¡Campo 'modulo' del ModuloModel!
                    },
                    'llamados' => function ($q) {
                        $q->select('id', 'id_asigna', 'hora_llamado', 'id_usuario', 'created_at');
                    }
                ])
                ->orderBy('created_at', 'asc'); // Para ordenar las asignaciones
            }
        ])
        ->whereBetween('fecha', [$request->fecha_ini, $request->fecha_fin])
        ->when($request->filled('id_sucursal'), function ($query) use ($request) {
            return $query->where('id_sucursal', $request->id_sucursal);
        })
        ->orderBy('hora_llegada', 'asc')
        ->get();

        if($turnos->isEmpty()){
            $this->message = 'No se encontraron turnos asignados en el día de hoy.';
        }else{
            $this->message = 'Registros obtenidos.';
        }
        $this->status  = 200;
        $this->error = false;
        $this->data    = $turnos; 
        return $this->respond(); 
    }

    public function estadisticasTurnos(Request $request)
    {
        $fechaInicio = $request->fecha_ini;
        $fechaFin = $request->fecha_fin;

        $turnos = TurnosModel::with([
            'prioritaria',
            'creador:id,nombre',
            // Carga ambas relaciones para poder usarlas
            'asignaciones.operario:id,nombre,id_profesion',
            'asignaciones.operarioReal:id,nombre,id_profesion',
            // Carga la profesión para ambas relaciones si es necesario, o solo para la que usas
            'asignaciones.operarioReal.profesion:id,profesion',
            'asignaciones.modulo:id,modulo',
            'asignaciones.sala:id,sala',
            'asignaciones.caso:id,caso',
        ])
        ->whereBetween('fecha', [$fechaInicio, $fechaFin])
        ->when($request->filled('id_sucursal'), function ($query) use ($request) {
            return $query->where('id_sucursal', $request->id_sucursal);
        })
        ->get();
        

        // Inicialización
        $totalAsignados = 0;
        $totalCancelados = 0;
        $totalAtendidos = 0;
        $porPrioritaria = [];
        $porOperario = [];
        $porUsuarioAsignador = [];
        $porSala = [];
        $porModulo = [];
        $porCaso = [];
        $tiemposPorOperario = [];
        $tiemposEsperaLlegadaAsignacionPorUsuario = [];
        $porProfesion = [];

        foreach ($turnos as $turno) {
            $creador = $turno->creador?->nombre ?? 'Desconocido';
            $prioritaria = $turno->prioritaria?->prioritaria ?? 'Sin prioritaria';

            $porPrioritaria[$prioritaria] = ($porPrioritaria[$prioritaria] ?? 0) + 1;

            if ($turno->hora_llegada && $turno->hora_asignacion) {
                $tiempoLlegadaAsignacion = strtotime($turno->hora_asignacion) - strtotime($turno->hora_llegada);

                if ($tiempoLlegadaAsignacion >= 0) {
                    if (!isset($tiemposEsperaLlegadaAsignacionPorUsuario[$creador])) {
                        $tiemposEsperaLlegadaAsignacionPorUsuario[$creador] = ['total_segundos' => 0, 'conteo' => 0];
                    }
                    $tiemposEsperaLlegadaAsignacionPorUsuario[$creador]['total_segundos'] += $tiempoLlegadaAsignacion;
                    $tiemposEsperaLlegadaAsignacionPorUsuario[$creador]['conteo']++;
                }
            }

            foreach ($turno->asignaciones as $asig) {
                $totalAsignados++;

                $caso = $asig->caso?->caso ?? 'Desconocido';
                $porCaso[$caso] = ($porCaso[$caso] ?? 0) + 1;

                if (strtolower($caso) === 'cancelado') {
                    $totalCancelados++;
                }

                // Si hay un operario real que atendió (id_operario_rea)
                if ($asig->id_operario_rea) {
                    $totalAtendidos++;
                    // Usa la nueva relación operarioReal
                    $nombreOperarioReal = $asig->operarioReal?->nombre ?? 'Desconocido';
                    $porOperario[$nombreOperarioReal] = ($porOperario[$nombreOperarioReal] ?? 0) + 1;

                    // Usa la nueva relación operarioReal para obtener la profesión
                    $profesion = $asig->operarioReal?->profesion?->profesion ?? 'Sin profesión';
                    $porProfesion[$profesion] = ($porProfesion[$profesion] ?? 0) + 1;

                    if ($asig->hora_asigna && $asig->hora_ini && $asig->hora_fin) {
                        $espera = strtotime($asig->hora_ini) - strtotime($asig->hora_asigna);
                        if($asig->hora_ini == $asig->hora_fin){
                            $atencion = strtotime($asig->hora_ope_fin) - strtotime($asig->hora_ope_ini);
                        }else{
                            $atencion = strtotime($asig->hora_fin) - strtotime($asig->hora_ini);
                        }
                        if (!isset($tiemposPorOperario[$nombreOperarioReal])) {
                            $tiemposPorOperario[$nombreOperarioReal] = ['total_espera_segundos' => 0, 'total_atencion_segundos' => 0, 'conteo' => 0];
                        }

                        $tiemposPorOperario[$nombreOperarioReal]['total_espera_segundos'] += $espera;
                        $tiemposPorOperario[$nombreOperarioReal]['total_atencion_segundos'] += $atencion;
                        $tiemposPorOperario[$nombreOperarioReal]['conteo']++;
                    }
                }

                // Aquí puedes decidir si quieres contar al operario asignado o al real.
                // Con el código actual, siempre usas el `creador` del turno.
                $porUsuarioAsignador[$creador] = ($porUsuarioAsignador[$creador] ?? 0) + 1;

                $sala = $asig->sala?->sala ?? 'Sin sala';
                $porSala[$sala] = ($porSala[$sala] ?? 0) + 1;

                $modulo = $asig->modulo?->modulo ?? 'Sin módulo';
                $porModulo[$modulo] = ($porModulo[$modulo] ?? 0) + 1;
            }
        }

        // ... (El resto del código para formatear y devolver los datos sigue igual)
        $formatear = function ($datosArray, $totalReferencia) {
            return collect($datosArray)->map(function ($cantidad, $nombre) use ($totalReferencia) {
                return [
                    'nombre' => $nombre,
                    'cantidad' => $cantidad,
                    'porcentaje' => round($cantidad * 100 / max($totalReferencia, 1), 2),
                    'promedio' => round($cantidad / max($totalReferencia, 1), 2)
                ];
            })->values();
        };

        $formatearTiempoHMS = function ($segundos) {
            $horas = floor($segundos / 3600);
            $minutos = floor(($segundos % 3600) / 60);
            $segundos = $segundos % 60;
            return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
        };

        $promediosTiemposOperarios = collect($tiemposPorOperario)->map(function ($valores, $nombreOperario) use ($formatearTiempoHMS) {
            $promedioEsperaSeg = round($valores['total_espera_segundos'] / max($valores['conteo'], 1));
            $promedioAtencionSeg = round($valores['total_atencion_segundos'] / max($valores['conteo'], 1));

            return [
                'operario_nombre' => $nombreOperario,
                'promedio_espera_segundos' => $promedioEsperaSeg,
                'promedio_espera_hms' => $formatearTiempoHMS($promedioEsperaSeg),
                'promedio_atencion_segundos' => $promedioAtencionSeg,
                'promedio_atencion_hms' => $formatearTiempoHMS($promedioAtencionSeg),
                'total_turnos_atendidos' => $valores['conteo']
            ];
        })->values();

        $totalesTiemposOperarios = collect($tiemposPorOperario)->map(function ($valores, $nombreOperario) use ($formatearTiempoHMS) {
            $horasTotales = round($valores['total_atencion_segundos'] / 3600, 2);
            return [
                'operario_nombre' => $nombreOperario,
                'total_atencion_segundos' => $valores['total_atencion_segundos'],
                'total_atencion_hms' => $formatearTiempoHMS($valores['total_atencion_segundos']),
                'total_atencion_horas' => $horasTotales,
                'total_turnos_atendidos' => $valores['conteo']
            ];
        })->values();

        $promediosTiempoLlegadaAsignacionPorUsuario = collect($tiemposEsperaLlegadaAsignacionPorUsuario)->map(function ($valores, $nombreUsuario) use ($formatearTiempoHMS) {
            $promedioSegundos = round($valores['total_segundos'] / max($valores['conteo'], 1));
            return [
                'usuario_nombre' => $nombreUsuario,
                'promedio_segundos' => $promedioSegundos,
                'promedio_hms' => $formatearTiempoHMS($promedioSegundos),
                'total_turnos_procesados' => $valores['conteo']
            ];
        })->values();

        $datos = [
            'resumen_general' => [
                'asignados' => $totalAsignados,
                'cancelados' => $totalCancelados,
                'atendidos' => $totalAtendidos,
            ],
            'por_prioritarias' => $formatear($porPrioritaria, $totalAsignados),
            'por_operarios_atendieron' => $formatear($porOperario, $totalAtendidos),
            'por_usuarios_asignadores' => $formatear($porUsuarioAsignador, $totalAsignados),
            'por_salasenatencion' => $formatear($porSala, $totalAsignados),
            'por_modulosenatencion' => $formatear($porModulo, $totalAsignados),
            'por_estado_caso' => $formatear($porCaso, $totalAsignados),
            'promedios_tiempos_operarios' => $promediosTiemposOperarios,
            'promedios_tiempo_recepcion_asignacion_por_usuario' => $promediosTiempoLlegadaAsignacionPorUsuario,
            'por_profesiones' => $formatear($porProfesion, $totalAtendidos),
            'totales_tiempos_operarios' => $totalesTiemposOperarios,
        ];

        $this->message = 'Estadísticas generadas correctamente.';
        $this->status = 200;
        $this->error = false;
        $this->data = $datos;
        return $this->respond();
    }

    public function registrarLlamado(Request $request)
    {
        try {
            $horaActual = Carbon::now()->toTimeString();
            $llamado = LlamadosModel::create([
                'id_asigna'    => $request->id_asigna,
                'hora_llamado' => $horaActual,
                'id_usuario'   => $request->id_usuario
            ]);

            if ($llamado) {
                $this->message = 'El llamado fue efectuado con éxito.';
                $this->status = 200;
                $this->error = false;
                $this->data = [];
            } else {
                $this->message = 'Se presentó un error al registrar el llamado.';
                $this->status = 400;
                $this->error = true;
                $this->data = null;
            }
        } catch (\Throwable $e) {
            $this->message = 'Error inesperado: ' . $e->getMessage();
            $this->status = 500;
            $this->error = true;
            $this->data = null;
        }

        return $this->respond();
    }

    public function disparar(Request $request){
        $turno = [
            "id"        => $request->id,
            "nombre"    => $request->nombre,
            "sala"      => $request->sala,
            "piso"      => $request->piso,
            "modulo"    => $request->modulo,
            "id_sucursal" => $request->id_sucursal
        ];

        event(new llamadoPantalla($turno));
        $this->message = 'El llamado fue efectuado con éxito.';
            $this->status = 200;
            $this->error = false;
            $this->data = [];
            return $this->respond();
    }

    public function dispararSeguimiento(Request $request){
        $turno = $this->listarTurnosDiarios($request, true)->toArray();
       
        $this->message = 'Actualización exitosa.';
        $this->status = 200;
        $this->error = false;
        $this->data = $turno;

        event(new SeguimientoDiario($turno));
        return $this->respond();
    }

    public function horaInicialTurno(Request $request){
       
        try{   
            log_actividades('turnos', 'Editar', $request, $request->all(), null);
            $turno = TurnosModel::find($request->id);
            $horaActual = Carbon::now()->toTimeString();
            if($turno){
                $actualizado = $turno->update([
                    "hora_ini"      => $horaActual,
                    "id_caso_turno"       => 5
                ]);

                $asignaciones = AsignacionesModel::find($request->id_asigna);
                $actualizado_asigna = $asignaciones->update([
                    "hora_ini"      => $horaActual,
                     "id_caso"       => 5
                ]);

                if($actualizado && $actualizado_asigna){
                    
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $turno;
                    $this->dispararSeguimiento($request);
                }else{
                    $this->message  = 'No se encontró el registro que desea actualizar.';
                    $this->status   = 200;
                    $this->error    = true;
                    $this->data     = [];
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

    public function restablecerHoraInicialTurno(Request $request){
       
        try{   
            log_actividades('turnos', 'Editar', $request, $request->all(), null);
            $turno = TurnosModel::find($request->id);

            if($turno){
                $actualizado = $turno->update([
                    "hora_ini"      => '00:00:00',
                    "id_caso_turno"       => 7
                ]);

                $asignaciones = AsignacionesModel::find($request->id_asigna);
                $actualizado_asigna = $asignaciones->update([
                    "hora_ini"      => '00:00:00',
                     "id_caso"       => 0
                ]);

                if($actualizado && $actualizado_asigna){
                    
                    $this->message  = 'El registro fue editado correctamente.';
                    $this->status   = 200;
                    $this->error    = false;
                    $this->data     = $turno;
                    $turno = $this->listarTurnosDiarios($request, true)->toArray();
                    event(new SeguimientoDiario($turno));
                }else{
                    $this->message  = 'No se encontró el registro que desea actualizar.';
                    $this->status   = 200;
                    $this->error    = true;
                    $this->data     = [];
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

    public function crearRegistro(Request $request)  //ASIGNAR TURNOS
    {
        try {
            $data = DB::connection('multi_empresa')->transaction(function () use ($request) {
                $validator = $this->validarDatosTurnos($request->all(), false);
                if ($validator->fails()) {
                    $this->message = $validator->errors();
                    $this->status  = 200;
                    $this->error   = true;
                    $this->data    = [];
                    return $this->respond();
                }

                try{
                
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
                            'hora_asignacion'   => $horaActual,
                            'hora_ini'          => '00:00:00',
                            'hora_fin'          => '00:00:00',
                            'activo'            => 0,
                            'id_caso_turno'     => 7,
                            'hora_cita'         => $request->hora_cita,
                            'id_prioritaria'    => $request->id_prioritaria,
                            'id_sucursal'       => $request->id_sucursal,
                            'id_usuario'        => $request->id_usuario,
                        ]);

                        $asignacion = AsignacionesModel::create([
                            'id_turno'      => $turno->id,
                            'id_operario'   => $request->id_operario,
                            'fecha'         => $fechaActual,
                            'hora_asigna'   => $horaActual,
                            'id_caso'           => 7,
                            'hora_ini'      => '00:00:00',
                            'hora_fin'      => '00:00:00',
                            'hora_ope_ini'      => $request->hor_ope_ini,
                            'hora_ope_fin'      => $horaActual,
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
                }catch(\Throwable $e){
                    $this->message = 'Error inesperado: ' . $e->getMessage();
                    $this->status  = 500;
                    $this->error   = true;
                    $this->data    = [];
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

    public function editarRegistro(Request $request)  
    {
        try {
            $data = DB::connection('multi_empresa')->transaction(function () use ($request) {
                $turno = TurnosModel::find($request->id);

                $horaActual = Carbon::now()->toTimeString();
                $fechaActual = Carbon::now()->toDateString();
                 $validator = $this->validarEditarDatosTurnos($request->all(), false);
                if ($validator->fails()) {
                    $this->message = $validator->errors();
                    $this->status  = 200;
                    $this->error   = true;
                    $this->data    = [];
                    return $this->respond();
                }
                try{
                    $actualizado = $turno->update([
                        "hora_asignacion"   => $horaActual,
                        "id_caso_turno"     => 7,
                        'id_prioritaria'    => $request->id_prioritaria,
                        'hora_cita'         => $request->hora_cita
                    ]); 
                    
                    $asignado = AsignacionesModel::where('id_turno', $request->id)->first();
                    $actualizado2 = $asignado->update([
                        "id_modulo" => $request->id_modulo,
                        'id_operario'   => $request->id_operario,
                        'id_operario_rea'   => $request->id_operario,
                        'hora_ini'   => $horaActual,
                        'hora_fin'   => $horaActual,
                    ]);
          
                    $actualizado3 = AsignacionesModel::create([
                        'id_turno'      => $request->id,
                        'id_operario'   => $request->id_operario,
                        'fecha'         => $fechaActual,
                        'hora_asigna'   => $horaActual,
                        'id_caso'           => 7,
                        'hora_ini'      => '00:00:00',
                        'hora_fin'      => '00:00:00',
                        'hora_ope_ini'      => $request->hor_ope_ini,
                        'hora_ope_fin'      => $horaActual,
                        'hora_cita'         => $request->hora_cita,
                        'id_usuario'    => $request->id_usuario,
                        'id_sala'       => $request->id_sala
                    ]);

                    if($actualizado && $actualizado2 && $actualizado3 ){
                        $this->message = 'El turno fue asignado con éxito.';
                        $this->status = 200;
                        $this->error = false;
                        $this->data = [];
                    }else{
                        $this->message = 'Se presentó un error al asignar el turno.';
                        $this->status = 200;
                        $this->error = false;
                        $this->data = [];
                    }
                }catch(\Throwable $e){
                    $this->message = 'Error inesperado: ' . $e->getMessage();
                    $this->status  = 500;
                    $this->error   = true;
                    $this->data    = [];
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

    public function actualizarLlamado(Request $request) //Llamado a pacientes
    {
        try {
            $data = DB::connection('multi_empresa')->transaction(function () use ($request) {

                $horaActual = Carbon::now()->toTimeString();
                $fechaActual = Carbon::now()->toDateString();
                if($request->id_caso == 2 || $request->id_caso == 5 || $request->id_caso == 6 || $request->id_caso == 7 ){
                    $hora_fin = '00:00:00';
                }else{
                    $hora_fin = $horaActual;
                }

                $asignacion = AsignacionesModel::find($request->id_asigna);
                if( $request->id_caso != 0){
                    $actualiza = $asignacion->update([
                        'id_modulo'         => $request->id_modulo,
                        'id_operario_rea'   => $request->id_usuario,
                        'hora_fin'          => $hora_fin,
                        'id_caso'           => $request->id_caso
                    ]);
                }

                if( $request->id_caso == 3){
                    // Simula error: este campo no existe => lanzará excepción
                    $asignacion = AsignacionesModel::create([
                        'id_turno'      => $request->id, // usa la clave primaria correcta
                        //'id_operario'   => $request->id_operario,
                        'fecha'         => $fechaActual,
                        'hora_asigna'   => $horaActual,
                        'hora_ini'      => '00:00:00',
                        'hora_fin'      => '00:00:00',
                        'id_usuario'    => $request->id_usuario,
                        'id_sala'       => $request->id_sala,
                        'hora_cita'     => $request->hora_cita,
                        'id_modulo'     => 0,
                        'id_operario'   => 0,
                        'id_operario_rea'   => 0,
                        'id_caso'       => 7
                    ]);
                }
                
                $turno = TurnosModel::find($request->id);
                if( $request->id_caso == 1 || $request->id_caso == 4){
                   
                    $actualiza = $turno->update([
                        'hora_fin' => $horaActual,
                    ]);

                    if($actualiza){
                        $this->message = 'El turno fue actualizado con éxito.';
                        $this->error = false;
                    }else{
                        $this->message = 'Se presentó un error al actualizar el turno.';
                        $this->error = true;
                    }
                }

                if( $request->id_caso == 0){
                    $actualiza = $asignacion->update([
                        'id_modulo'         => $request->id_modulo,
                        'id_operario'   => $request->id_usuario,
                        'id_operario_rea'   => $request->id_usuario,
                        'hora_ini'          => $horaActual,
                        'hora_fin'          => $horaActual,
                        'id_caso'           => 4,
                        'hora_ope_ini'      => $request->hor_ope_ini,
                        'hora_ope_fin'      => $horaActual,
                    ]);
                    $turno = TurnosModel::find($request->id);
                    $actualizaTurno = $turno->update([
                        'hora_ini'  => $request->hor_ini,
                        'hora_fin'         => $horaActual,
                        'id_caso_turno'    => 4
                    ]);

                    if($actualiza && $actualizaTurno){
                        $this->message = 'El turno fue actualizado con éxito.';
                        $this->status = 200;
                        $this->error = false;
                        $this->data = [];
                    }else{
                        $this->message = 'Se presentó un error al actualizar el turno.';
                        $this->status = 200;
                        $this->error = true;
                        $this->data = [];
                    }
                }
                
                if( $request->id_caso == 2){
                    $this->restablecerHoraInicialTurno($request);
                }

                $actualiza2 = true;
                if($request->id_caso != 0){  
                    $actualiza2 = $turno->update([
                        'id_caso_turno' => $request->id_caso,
                    ]);
                }
                if($actualiza2){
                    $this->dispararSeguimiento($request);
                    $this->message = 'El turno fue actualizado con éxito.';
                    $this->status = 200;
                    $this->error = false;
                    $this->data = [$asignacion];
                }else{
                    $this->message = 'Se presentó un error al actualizar el turno.';
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

    public function ultimosPacientes(Request $request){
       $turnos = TurnosModel::with([
        // Operario con profesión y campos seleccionados
        'asignaciones.operario' => function ($q) {
            $q->select('id', 'nombre', 'celular', 'email', 'id_profesion')
                ->with([
                    'profesion' => function ($q) {
                        $q->select('id', 'profesion');
                    }
                ]);
        },
        // Usuario que hizo la asignación (id_usuario)
        'asignaciones.usuarios' => function ($q) {
            $q->select('id', 'nombre', 'celular', 'email');
        },
        // Caso relacionado
        'asignaciones.caso',
        // Sala con piso
        'asignaciones.sala' => function ($q) {
            $q->with([
                'piso' => function ($q) {
                    $q->select('id', 'piso');
                }
            ]);
        },
        // Prioridad del turno
        'prioritaria' => function ($q) {
            $q->select('id', 'prioritaria');
        },
        'paciente' => function ($q) {
            $q->select('id', 'nombre');
        }
    ])
    ->whereHas('asignaciones', function ($q) {
        $q->where('id_caso', '5');
    })
    ->where('id_sucursal', $request->id_sucursal)
    ->get();
        $this->message = 'Últimos turnos.'.$request->id_sucursal;
        $this->status = 200;
        $this->error = false;
        $this->data = $turnos;
        return $this->respond();
    }
}
