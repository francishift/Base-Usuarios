<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para la actualización de usuarios existentes.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * El middleware de ruta ya garantiza que solo el admin llega aquí.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para actualizar un usuario.
     * El email es único excepto para el propio usuario que se edita.
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', "unique:users,email,{$userId}"],
            'rol'      => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults()],
        ];
    }

    /**
     * Mensajes de error en castellano.
     */
    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es obligatorio.',
            'name.max'       => 'El nombre no puede superar los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'El formato del correo electrónico no es válido.',
            'email.unique'   => 'Este correo ya está registrado en otro usuario.',
            'rol.required'   => 'Debes asignar un rol al usuario.',
            'rol.exists'     => 'El rol seleccionado no es válido.',
        ];
    }
}
