<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaProfesionesTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validarDatosProfesiones(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        $mensajes = [];
        
        $rules = [
            'profesion'  => [
                'required',
                $esEditar
                    ? Rule::unique('empresa_base.profesiones', 'profesion')->ignore($idActual)
                    : Rule::unique('empresa_base.profesiones', 'profesion'),
            ],
            'id_usuario'=> 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1'
        ];

        $mensajes = [
            'profesion.required'   => 'Falta ingresar el nombre de la profesión',
            'profesion.unique'     => 'Ya existe una profesión con este este nombre.',
            'id_usuario.required' => 'Debe asignar un usuario responsable.',
            'id_usuario.integer'  => 'El usuario debe ser un número válido.',
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sucursal.required'=> 'Falta ingresar la sucursal de donde se crea o se editar la sucursal.',
            'id_sucursal.min'=> 'Falta ingresar la sucursal de donde se crea o se editar la sucursal.',
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
