<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaTurnosTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    
    public function validarDatosTurnos (array $data)
    {
        $rules = [
            'id_sala' => 'integer|min:1',
            'id_usuario' => 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1',
            'hora_cita' => 'required',
            'id_prioritaria' => 'integer|min:1',
            'id_operario' => 'integer|min:1',
        ];

        $mensajes = [
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sala.min' => 'Falta señalar la sala en la cual se llevará a cabo la atención.',
            'id_sucursal.required' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_sucursal.min' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_prioritaria.min' => 'Falta seleccionar el tipo de prioridad o, en su defecto señalar NO APLICA. ',
            'hora_cita.required' => 'Falta ingresar la hora de la cita.',
            'id_operario.min' => 'Falta señalar el profesional al cual se le asignará el turno.',
        ];

        return Validator::make($data, $rules, $mensajes);
    }

    public function validarDatosTurnosPantalla (array $data)
    {
        $rules = [
            'id_sala' => 'integer|min:1',
            'id_usuario' => 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1',
        ];

        $mensajes = [
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sala.min' => 'Falta señalar la sala en la cual se llevará a cabo la atención.',
            'id_sucursal.required' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_sucursal.min' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
        ];

        return Validator::make($data, $rules, $mensajes);
    }

    public function validarEditarDatosTurnos (array $data)
    {
        $rules = [
            'id_sala' => 'integer|min:1',
            'id_usuario' => 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1',
            'id_operario' => 'integer|min:1',
        ];

        $mensajes = [
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sala.min' => 'Falta señalar la sala en la cual se llevará a cabo la atención.',
            'id_sucursal.required' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_sucursal.min' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_operario.min' => 'Falta señalar el profesional al cual se le asignará el turno.',
  
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
