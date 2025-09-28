<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SucursalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
       /*  'sucursal' => [
            'required',
            Rule::unique('multi_empresa.sucursales', 'sucursal'),
        ],
         */
    ];
    
    }

    /* public function crearEditar($request){
        $id = $request->id;
        $rules = [
            "id" => ['required'],
            "sucursal" => ['required', 'unique:multi_empresa.sucursales,sucursal'],
            "direccion" => ['required'],
            "id_ciudad" => ['required'],
            "id_usuario" => ['required'],
        ];

        if ($id) {
            // Si es edición, ignoramos ese ID en la validación de unicidad
            $rules["sucursal"] = ['required', Rule::unique('sucursales', 'sucursal')->ignore($id)];
        }

        return $request->validate($rules, [
            "id.required" => "Falta ingresar el ID o en su defecto debe venir null.",
            "sucursal.required" => "Falta ingresar el nombre de la sucursal.",
            "sucursal.unique" => "La sucursal ya se encuentra registrado en el sistema.",
            "direccion.required" => "Falta ingresar la dirección de la sucursal.",
            "id_ciudad.required" => "Falta ingresar la ciudad de la sucursal.",
            "id_usuario.required" => "Falta ingresar el usuario que crea o edita el registro.",
        ]);
    } */
}
