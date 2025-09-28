<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaPisosTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validarDatosPisos(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        $mensajes = [];

        $uniquePiso = Rule::unique('empresa_base.pisos', 'piso')
            ->where(fn ($query) => $query->where('id_sucursal', $data['id_sucursal']  ?? 0)); 

        if ($esEditar && $idActual) {
            $uniquePiso->ignore($idActual);
        }

        $rules = [
            'piso' => [
                'required',
                $uniquePiso,
            ],
            'id_usuario'=> 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1'
        ];

        $mensajes = [
            'piso.required'   => 'Falta ingresar el nombre del piso.',
            'piso.unique'     => 'Ya existe un piso con este nombre.',
            'id_usuario.required' => 'Debe asignar un usuario responsable.',
            'id_usuario.integer'  => 'El usuario debe ser un número válido.',
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sucursal.required'=> 'Falta ingresar la sucursal de donde se crea o se editar la sucursal.',
            'id_sucursal.min'=> 'Falta ingresar la sucursal de donde se crea o se editar la sucursal.',
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
