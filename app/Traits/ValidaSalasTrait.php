<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaSalasTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    
    public function validarDatosSalas(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        // Normalizar el nombre de la sala
        $data['sala'] = trim(strtolower($data['sala'] ?? ''));

        $uniqueSala = Rule::unique('empresa_base.salas', 'sala')
            ->where(fn ($query) => $query->where('id_piso', $data['id_piso'] ?? 0)
                ->where('id_sucursal', $data['id_sucursal'] ?? 0));

        if ($esEditar && $idActual) {
            $uniqueSala->ignore($idActual);
        }

        $rules = [
            'sala' => ['required', $uniqueSala],
            'id_piso' => 'required|integer|min:1',
            'id_usuario' => 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1'
        ];

        $mensajes = [
            'sala.required' => 'Falta ingresar el nombre de la sala.',
            'sala.unique' => 'Ya existe una sala con este nombre en ese piso y sucursal.',
            'id_piso.required' => 'Falta señalar el piso al cual pertenece la sala.',
            'id_usuario.required' => 'Debe asignar un usuario responsable.',
            'id_usuario.integer' => 'El usuario debe ser un número válido.',
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sucursal.required' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_sucursal.min' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
