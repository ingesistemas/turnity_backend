<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
            //
        ];
    }

    public function crearEditar($request){
        $id = $request->id;
        $rules = [
            "id" => ['required'],
            "rol" => ['required', 'unique:roles,rol'],
            "id_usuario" => ['required'],
        ];

        if ($id) {
            // Si es edición, ignoramos ese ID en la validación de unicidad
            $rules["rol"] = ['required', Rule::unique('roles', 'rol')->ignore($id)];
        }

        return $request->validate($rules, [
            "id.required" => "Falta ingresar el ID o en su defecto debe venir null.",
            "rol.required" => "Falta ingresar el nombre del Rol.",
            "rol.unique" => "El Rol ya se encuentra registrado en el sistema.",
            "id_usuario.required" => "Falta ingresar el usuario que crea o edita el registro.",
        ]);
    }
}
