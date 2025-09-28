<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidarEditarClaveTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validarDatosclave(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        $rules = [
            'password'  => [
                'required',
               
            ],
            'email' => [
                'required',
                
            ],
            'id_usuario'=> 'required|integer',
            "id_sucursal" => 'required'
        ];

        $mensajes = [
            'password.required'   => 'Falta ingresar la constraseña.',
            'email.required'   => 'Falta ingresar el correo electrónico.',
            'id_usuario.required' => 'Debe asignar un usuario responsable de la creación.',
            'id_usuario.integer'  => 'El usuario debe ser un número válido.',
            'id_sucursal.required' => 'Falta ingresar la sucursal.'
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
