<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso API para el modelo User.
 *
 * Formatea, filtra y securiza los datos antes de
 * enviarlos al frontend. Nunca se pasa Eloquent crudo.
 */
class UserResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     * Solo expone los campos estrictamente necesarios.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'email'  => $this->email,
            'active' => $this->active,
            'rol'    => $this->roles->first()?->name,
        ];
    }
}
