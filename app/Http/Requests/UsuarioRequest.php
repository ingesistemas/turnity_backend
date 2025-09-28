<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
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
            "nombre" => ['required', 'unique:usuarios,nombre'],
            "celular" => ['required', 'unique:usuarios,celular'],
            "email" => ['required|email', 'unique:usuarios,email'],
            "id_rol" => ['required'],
            "id_sucursal" => ['required'],
            "id_usuario" => ['required'],
        ];

        if ($id) {
            // Si es edición, ignoramos ese ID en la validación de unicidad
            $rules["email"] = ['required', Rule::unique('usuarios', 'email')->ignore($id)];
            $rules["nombre"] = ['required', Rule::unique('usuarios', 'nombre')->ignore($id)];
            $rules["celular"] =['required','digits:10',Rule::unique('usuarios', 'celular')->ignore($id)];
            //$rules["celular"] = ['required', Rule::unique('usuarios', 'celular')->ignore($id)];
        }

        return $request->validate($rules, [
            "id.required" => "Falta ingresar el ID o en su defecto debe venir null.",
            "nombre.required" => "Falta ingresar el nombre del usuario.",
            "nombre.uniqued" => "El nombre ya se encuentra registrado en el sistema.",
            "celular.required" => "Falta ingresar el número del celular.",
            "celular.unique" => "El número de celular ya se encuentra registrado en el sistema.",
            "celular.digits" => "El número de celular debe tener 10 dígitos.",
            "email.required" => "Falta ingresar el correo electrónico.",
            "email.unique" => "El correo electrónico ya se encuentra registrado en el sistema.",
            "id_rol.required" => "Falta ingresar el rol.",
            "id_sucursal.required" => "Falta ingresar la sucursal.",
        ]);
    }
}
