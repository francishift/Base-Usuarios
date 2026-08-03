<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\BienvenidaUsuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Servicio para la gestión de usuarios.
 *
 * Centraliza toda la lógica de negocio relacionada con usuarios:
 * creación, actualización, activación/desactivación y eliminación.
 *
 * El controlador es delgado (thin controller) y delega aquí.
 */
class UserService
{
    /**
     * Campos permitidos para ordenación.
     * Whitelist de seguridad: el campo de ordenación no se parametriza en SQL.
     */
    private const CAMPOS_ORDENACION = ['name', 'email', 'active', 'created_at'];

    /**
     * Retorna el listado de usuarios con soporte de búsqueda, ordenación y paginación server-side.
     * Diseñado para soportar miles de registros sin afectar al rendimiento.
     *
     * @param  string  $search     Texto de búsqueda (nombre o email)
     * @param  string  $sortField  Campo de ordenación (whitelist)
     * @param  string  $sortOrder  Dirección: 'asc' | 'desc'
     * @param  int     $perPage    Registros por página
     */
    public function listar(
        string $search    = '',
        string $sortField = 'name',
        string $sortOrder = 'asc',
        int    $perPage   = 20,
    ): LengthAwarePaginator {
        // Validación de campo de ordenación (whitelist)
        $sortField = in_array($sortField, self::CAMPOS_ORDENACION, true) ? $sortField : 'name';
        $sortOrder = $sortOrder === 'desc' ? 'desc' : 'asc';

        return User::with('roles')
            ->when($search, function ($query) use ($search) {
                // Búsqueda por nombre o email con LIKE (case-insensitive)
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo usuario con contraseña temporal y le asigna un rol.
     * Envía notificación por email con las credenciales de acceso.
     *
     * @param  array{name: string, email: string, rol: string}  $datos
     */
    public function crear(array $datos): User
    {
        // Si el admin introdujo contraseña personalizada la usamos, sino generamos una temporal
        $passwordTemporal = !empty($datos['password']) ? $datos['password'] : Str::password(12);

        $usuario = User::create([
            'name'     => $datos['name'],
            'email'    => $datos['email'],
            'password' => Hash::make($passwordTemporal),
            'active'   => true,
        ]);

        // Asignamos el rol indicado
        $usuario->assignRole($datos['rol']);

        // Notificamos al usuario por email con sus credenciales
        $usuario->notify(new BienvenidaUsuario($passwordTemporal));

        return $usuario;
    }

    /**
     * Actualiza los datos de un usuario existente, su rol y opcionalmente su contraseña.
     *
     * @param  array{name: string, email: string, rol: string, password?: string|null}  $datos
     */
    public function actualizar(User $usuario, array $datos): User
    {
        $payload = [
            'name'  => $datos['name'],
            'email' => $datos['email'],
        ];

        // Si el admin introdujo una nueva contraseña, la actualizamos
        if (!empty($datos['password'])) {
            $payload['password'] = Hash::make($datos['password']);
        }

        $usuario->update($payload);

        // syncRoles reemplaza todos los roles del usuario por el nuevo
        $usuario->syncRoles([$datos['rol']]);

        return $usuario;
    }

    /**
     * Alterna el estado activo/inactivo de un usuario.
     * No permite desactivar la propia cuenta del usuario autenticado.
     *
     * @throws \RuntimeException Si se intenta desactivar la cuenta propia
     */
    public function alternarEstado(User $usuario): User
    {
        if ($usuario->id === auth()->id()) {
            throw new \RuntimeException('No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['active' => ! $usuario->active]);

        return $usuario->fresh();
    }

    /**
     * Elimina un usuario del sistema (Soft Delete).
     * No permite eliminar la propia cuenta del usuario autenticado.
     *
     * @throws \RuntimeException Si se intenta eliminar la cuenta propia
     */
    public function eliminar(User $usuario): void
    {
        if ($usuario->id === auth()->id()) {
            throw new \RuntimeException('No puedes eliminar tu propia cuenta.');
        }

        $usuario->delete();
    }

    /**
     * Retorna el listado de usuarios eliminados suavemente (papelera).
     */
    public function listarPapelera(
        string $search    = '',
        string $sortField = 'name',
        string $sortOrder = 'asc',
        int    $perPage   = 20,
    ): LengthAwarePaginator {
        $sortField = in_array($sortField, self::CAMPOS_ORDENACION, true) ? $sortField : 'name';
        $sortOrder = $sortOrder === 'desc' ? 'desc' : 'asc';

        return User::onlyTrashed()
            ->with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage);
    }

    /**
     * Restaura un usuario previamente eliminado a la papelera.
     */
    public function restaurar(int $id): User
    {
        $usuario = User::onlyTrashed()->findOrFail($id);
        $usuario->restore();

        return $usuario;
    }

    /**
     * Elimina definitivamente a un usuario de la papelera en base de datos.
     *
     * @throws \RuntimeException Si se intenta eliminar la cuenta propia
     */
    public function eliminarDefinitivo(int $id): void
    {
        if ($id === auth()->id()) {
            throw new \RuntimeException('No puedes eliminar tu propia cuenta.');
        }

        $usuario = User::onlyTrashed()->findOrFail($id);
        $usuario->forceDelete();
    }
}
