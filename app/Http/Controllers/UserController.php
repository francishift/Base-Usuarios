<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

/**
 * Controlador de gestión de usuarios.
 *
 * Thin controller: solo orquesta la petición.
 * La lógica de negocio reside en UserService.
 * Los datos al frontend pasan siempre por UserResource.
 */
class UserController extends Controller
{
    /**
     * Inyectamos el servicio de usuarios via constructor.
     */
    public function __construct(
        protected readonly UserService $userService
    ) {}

    /**
     * Listado paginado de usuarios con soporte de búsqueda y ordenación server-side.
     * La lógica de filtrado reside en UserService::listar() para mantener thin controller.
     */
    public function index(Request $request)
    {
        $usuarios = $this->userService->listar(
            search:    $request->string('search', ''),
            sortField: $request->string('sortField', 'name'),
            sortOrder: $request->string('sortOrder', 'asc'),
        );

        return Inertia::render('Usuarios/Index', [
            'usuarios'       => UserResource::collection($usuarios),
            'filtros'        => $request->only(['search', 'sortField', 'sortOrder']),
            'roles'          => Role::orderBy('name')->pluck('name'),
            'esPapelera'     => false,
            'conteoPapelera' => User::onlyTrashed()->count(),
        ]);
    }

    /**
     * Listado de usuarios en la papelera (Soft Deleted).
     */
    public function trashed(Request $request)
    {
        $usuarios = $this->userService->listarPapelera(
            search:    $request->string('search', ''),
            sortField: $request->string('sortField', 'name'),
            sortOrder: $request->string('sortOrder', 'asc'),
        );

        return Inertia::render('Usuarios/Index', [
            'usuarios'       => UserResource::collection($usuarios),
            'filtros'        => $request->only(['search', 'sortField', 'sortOrder']),
            'roles'          => Role::orderBy('name')->pluck('name'),
            'esPapelera'     => true,
            'conteoPapelera' => User::onlyTrashed()->count(),
        ]);
    }

    /**
     * Formulario de creación de usuario.
     */
    public function create()
    {
        return Inertia::render('Usuarios/Create', [
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Almacena un nuevo usuario.
     * La validación ya viene del StoreUserRequest.
     */
    public function store(StoreUserRequest $request)
    {
        $usuario = $this->userService->crear($request->validated());

        return redirect()
            ->route('usuarios.index')
            ->with('success', "Usuario {$usuario->name} creado correctamente. Se ha enviado un email con las credenciales.");
    }

    /**
     * Formulario de edición de usuario.
     */
    public function edit(User $user)
    {
        return Inertia::render('Usuarios/Edit', [
            'usuario' => (new UserResource($user->load('roles')))->resolve(),
            'roles'   => Role::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Actualiza los datos de un usuario existente.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->actualizar($user, $request->validated());

        return redirect()
            ->route('usuarios.index')
            ->with('success', "Usuario {$user->name} actualizado correctamente.");
    }

    /**
     * Activa o desactiva un usuario.
     */
    public function toggleActive(User $user)
    {
        try {
            $usuario = $this->userService->alternarEstado($user);
            $estado  = $usuario->active ? 'activado' : 'desactivado';

            return back()->with('success', "Usuario {$usuario->name} {$estado} correctamente.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Elimina suavemente a un usuario del sistema (Soft Delete).
     */
    public function destroy(User $user)
    {
        try {
            $nombre = $user->name;
            $this->userService->eliminar($user);

            return redirect()
                ->route('usuarios.index')
                ->with('success', "Usuario {$nombre} enviado a la papelera.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Restaura un usuario desde la papelera.
     */
    public function restore(int $id)
    {
        try {
            $usuario = $this->userService->restaurar($id);

            return back()->with('success', "Usuario {$usuario->name} restaurado correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo restaurar el usuario.');
        }
    }

    /**
     * Elimina definitivamente a un usuario de la papelera en base de datos.
     */
    public function forceDelete(int $id)
    {
        try {
            $this->userService->eliminarDefinitivo($id);

            return back()->with('success', "Usuario eliminado definitivamente de la base de datos.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo eliminar el usuario.');
        }
    }
}
