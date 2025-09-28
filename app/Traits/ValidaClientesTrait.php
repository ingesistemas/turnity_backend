<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidaClientesTrait
{
    /**
     * Valida los datos de una sucursal.
     *
     * @param  array  $data
     * @param  bool   $esEditar
     * @param  int|null $idActual
     * @return \Illuminate\Contracts\Validation\Validator
     */
    
    public function validarDatosClientes(array $data, bool $esEditar = false, ?int $idActual = null)
    {
        // Normalizar el nombre de la sala
        $data['documento'] = trim(strtolower($data['documento'] ?? ''));

        $uniqueCliente = Rule::unique('empresa_base.clientes', 'documento')
            ->where(fn ($query) => $query->where('id_cliente', $data['id_cliente'] ?? 0));

        if ($esEditar && $idActual) {
            $uniqueCliente->ignore($idActual);
        }

        $rules = [
            'documento' => ['required', $uniqueCliente],
            'id_usuario' => 'required|integer|min:1',
            'id_sucursal' => 'required|integer|min:1'
        ];

        $mensajes = [
            'documento.required' => 'Falta ingresar el número del documento.',
            'documento.unique' => 'Ya existe este número de documento registrado en el sistema.',
            'cliente.required' => 'Falta ingresar el nombre del paciente.',
            'id_usuario.required' => 'Debe asignar un usuario responsable.',
            'id_usuario.integer' => 'El usuario debe ser un número válido.',
            'id_usuario.min' => 'Debe asignar un usuario responsable.',
            'id_sucursal.required' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
            'id_sucursal.min' => 'Falta ingresar la sucursal de donde se crea o se edita la sala.',
           
        ];

        return Validator::make($data, $rules, $mensajes);
    }
}
