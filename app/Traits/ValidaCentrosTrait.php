<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaCentrosTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    
    public function validarDatosCentros(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        // Normalizar el nombre de la sala
        $data['centro'] = trim(strtolower($data['centro'] ?? ''));

        $uniqueSala = Rule::unique('empresa_base.centros', 'centro')
            ->where(fn ($query) => $query->where('id_sucursal', $data['id_sucursal'] ?? 0));

        if ($esEditar && $idActual) {
            $uniqueSala->ignore($idActual);
        }

        $rules = [
            'centro' => ['required', $uniqueSala],
            'id_usuario' => 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1'
        ];

        $mensajes = [
            'centro.required' => 'Falta ingresar el nombre del centro de atención.',
            'centro.unique' => 'Ya existe un centro de atención con este nombre en ese piso y sucursal.',
            'id_usuario.required' => 'Debe asignar un usuario responsable.',
            'id_usuario.integer' => 'El usuario debe ser un número válido.',
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sucursal.required' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_sucursal.min' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
           
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
