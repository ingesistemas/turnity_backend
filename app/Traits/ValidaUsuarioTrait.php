<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaUsuarioTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validarDatosUsuario(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        $rules = [
            'nombre'  => [
                'required',
                $esEditar
                    ? Rule::unique('empresa_base.usuarios', 'nombre')->ignore($idActual)
                    : Rule::unique('empresa_base.usuarios', 'nombre')
            ],
            'celular' => [
                'required',
                $esEditar
                    ? Rule::unique('empresa_base.usuarios', 'celular')->ignore($idActual)
                    : Rule::unique('empresa_base.usuarios', 'celular'),
            ],
            'email' => [
                'required',
                $esEditar
                    ? Rule::unique('empresa_base.usuarios', 'email')->ignore($idActual)
                    : Rule::unique('empresa_base.usuarios', 'email'),
            ],
            'id_usuario'=> 'required|integer',
            "id_profesion" => 'required',
            "id_sucursal" => 'required'
        ];

        $mensajes = [
            'nombre.required'   => 'Falta ingresar el nombre completo del usuario.',
            'nombre.unique'     => 'Este nombre ya se encuentra registrado en el sistema.',
            'celular.required'  => 'Falta ingresar el número del celular.',
            'celular.unique'  => 'El número de celular ya se encuentra registrado en el sistema.',
            'email.required'   => 'Falta ingresar el correo electrónico.',
            'email.unique'       => 'El correo electrónico ya se encuentra registrado en el sistema.',
            'id_usuario.required' => 'Debe asignar un usuario responsable de la creación.',
            'id_usuario.integer'  => 'El usuario debe ser un número válido.',
            'id_profesion.required' => 'Falta digitar la profesión.',
            'id_sucursal.required' => 'Falta ingresar la sucursal.'
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
