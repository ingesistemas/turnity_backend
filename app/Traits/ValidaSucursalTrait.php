<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaSucursalTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validarDatosSucursal(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        $mensajes = [];
        
        $rules = [
            'sucursal'  => [
                'required',
                $esEditar
                    ? Rule::unique('empresa_base.sucursales', 'sucursal')->ignore($idActual)
                    : Rule::unique('empresa_base.sucursales', 'sucursal'),
            ],
            'direccion' => 'required|string|max:255',
            'email'     => 'required|email',
            'id_ciudad' => 'required|integer|min:1',
            'id_usuario'=> 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1'
        ];

        $mensajes = [
            'sucursal.required'   => 'La sucursal es obligatoria.',
            'sucursal.unique'     => 'Ya existe una sucursal con ese nombre.',
            'direccion.required'  => 'La dirección es obligatoria.',
            'id_ciudad.required'  => 'Debe seleccionar una ciudad.',
            'id_ciudad.integer'   => 'La ciudad debe ser un número válido.',
            'id_ciudad.min'  => 'Debe seleccionar una ciudad.',
            'email.required'      => 'El correo electrónico es obligatorio.',
            'email.email'         => 'El formato del correo electrónico no es válido.',
            'id_usuario.required' => 'Debe asignar un usuario responsable.',
            'id_usuario.integer'  => 'El usuario debe ser un número válido.',
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sucursal.required'=> 'Falta ingresar la sucursal de donde se crea o se editar la sucursal.',
            'id_sucursal.min'=> 'Falta ingresar la sucursal de donde se crea o se editar la sucursal.',
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
