<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaModulosTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    
    public function validarDatosModulos(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        // Normalizar el nombre de la sala
        $data['modulo'] = trim(strtolower($data['modulo'] ?? ''));

        $uniqueModulo = Rule::unique('empresa_base.modulos', 'modulo')
            ->where(fn ($query) => $query->where('id_sala', $data['id_sala'] ?? 0)
                ->where('id_sucursal', $data['id_sucursal'] ?? 0));

        if ($esEditar && $idActual) {
            $uniqueModulo->ignore($idActual);
        }

        $rules = [
            'modulo' => ['required', $uniqueModulo],
            'id_sala' => 'required|integer|min:1',
            'id_usuario' => 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1'
        ];

        $mensajes = [
            'modullo.required' => 'Falta ingresar el nombre del módulo de atención.',
            'modulo.unique' => 'Ya existe un módulo de atención con este nombre en ese piso y sucursal.',
            'id_usuario.required' => 'Debe asignar un usuario responsable.',
            'id_usuario.integer' => 'El usuario debe ser un número válido.',
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sucursal.required' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_sucursal.min' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_sala.required' => 'Falta indicar la sala al que pertenece el centro de atención.'
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
