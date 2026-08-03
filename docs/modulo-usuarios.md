# Módulo de Gestión de Usuarios

## 1. Descripción General

El **Módulo de Gestión de Usuarios** es el núcleo de administración del sistema **CMS Base de Usuarios**. Proporciona una interfaz completa, segura y optimizada para administrar los usuarios registrados en el sistema, permitiendo su creación, edición, activación/desactivación y eliminación.

El acceso a este módulo está estrictamente restringido a usuarios con el rol `admin` mediante el middleware de autorización de Spatie `role:admin`.

---

## 2. Roles del Sistema

El sistema implementa control de acceso basado en roles (RBAC) mediante `spatie/laravel-permission`:

| Clave del Rol | Nombre Visible (Etiqueta) | Descripción y Permisos | Color Badge (Modo Claro / Oscuro) |
|---|---|---|---|
| `admin` | **Administrador** | Acceso total al sistema y gestión completa de usuarios y configuraciones. | `bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300` |
| `gestor` | **Gestor** | Gestión operacional de contenidos y recursos del sistema. | `bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300` |
| `cliente` | **Cliente** | Acceso de cliente asignado al sistema. | `bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300` |

### Escalabilidad de Roles
Para incorporar un nuevo rol en el futuro:
1. Crear el rol en base de datos: `Spatie\Permission\Models\Role::create(['name' => 'nuevo_rol']);`.
2. Registrar la etiqueta legible y las clases de badge en `resources/js/Composables/useRoles.js`.
3. Todos los selectores de formularios y vistas de la aplicación reconocerán el nuevo rol automáticamente sin cambios adicionales en código.

---

## 3. Matriz de Rutas API y Controladores

Todas las rutas están agrupadas bajo el prefijo `/usuarios`, protegidas por el grupo de middleware `['auth', 'role:admin']`:

| Método HTTP | Ruta URI | Nombre de Ruta | Acción en `UserController` | Descripción |
|---|---|---|---|---|
| `GET` | `/usuarios` | `usuarios.index` | `index(Request $request)` | Listado paginado de usuarios activos con búsqueda y ordenación server-side |
| `GET` | `/usuarios/papelera` | `usuarios.trashed` | `trashed(Request $request)` | Listado paginado de usuarios eliminados suavemente (Papelera) |
| `GET` | `/usuarios/crear` | `usuarios.create` | `create()` | Formulario de creación de nuevo usuario |
| `POST` | `/usuarios` | `usuarios.store` | `store(StoreUserRequest $request)` | Procesa la creación y envía correo de bienvenida |
| `GET` | `/usuarios/{user}/editar` | `usuarios.edit` | `edit(User $user)` | Formulario de edición con datos resueltos por `UserResource` |
| `PUT` | `/usuarios/{user}` | `usuarios.update` | `update(UpdateUserRequest $request, User $user)` | Guarda los cambios del usuario, contraseña opcional y rol |
| `PATCH` | `/usuarios/{user}/activar` | `usuarios.toggle-active` | `toggleActive(User $user)` | Alterna el estado `active` (true/false) |
| `PATCH` | `/usuarios/{id}/restaurar` | `usuarios.restore` | `restore(int $id)` | Restaura un usuario desde la papelera |
| `DELETE` | `/usuarios/{user}` | `usuarios.destroy` | `destroy(User $user)` | Elimina suavemente al usuario (Soft Delete) |
| `DELETE` | `/usuarios/{id}/definitivo` | `usuarios.force-delete` | `forceDelete(int $id)` | Elimina de forma permanente al usuario de la base de datos |

---

## 4. Arquitectura de Código (Backend)

Sigue los patrones **Thin Controller** y **Clean Architecture**:

- **`UserController`** (`app/Http/Controllers/UserController.php`):
  Orquesta la recepción de peticiones HTTP, delega la lógica de negocio a `UserService` y devuelve respuestas formateadas a Inertia mediante `UserResource`.
- **`UserService`** (`app/Services/UserService.php`):
  Contiene la lógica de negocio:
  - `listar(...)`: Búsqueda por `name` o `email` con ordenación dinámica basada en whitelist (`self::CAMPOS_ORDENACION = ['name', 'email', 'active', 'created_at']`) y paginación a 20 registros por página (`paginate(20)`).
  - `crear(...)`: Permite contraseña personalizada o genera una temporal aleatoria de 12 caracteres (`Str::password(12)`), crea el usuario con `active = true`, asigna el rol e informa por correo.
  - `actualizar(...)`: Actualiza campos, encripta nueva contraseña (si fue provista) y sincroniza el rol.
  - `alternarEstado(...)`: Alterna la bandera `active`. Lanza `RuntimeException` si el usuario intenta desactivarse a sí mismo.
  - `eliminar(...)`: Realiza Soft Delete (`$usuario->delete()`).
  - `listarPapelera(...)`: Retorna listado de usuarios en papelera (`User::onlyTrashed()`).
  - `restaurar(...)`: Restaura un usuario previamente borrado (`$usuario->restore()`).
  - `eliminarDefinitivo(...)`: Borra permanentemente el usuario de la base de datos (`$usuario->forceDelete()`).
- **`UserResource`** (`app/Http/Resources/UserResource.php`):
  Filtra y expone de forma segura los datos enviados al frontend (`id`, `name`, `email`, `active`, `rol`, `deleted_at`).
- **`VerificarUsuarioActivo`** (`app/Http/Middleware/VerificarUsuarioActivo.php`):
  Middleware de sesión que intercepta cada petición. Si un usuario autenticado tiene `active == false`, fuerza un logout inmediato de la sesión, invalida el token y lo redirige a `/login` informando de la desactivación.

---

## 5. Componentes Frontend (Vue 3 + PrimeVue)

- **`resources/js/Pages/Usuarios/Index.vue`**:
  Renderiza la tabla de usuarios utilizando PrimeVue `DataTable` con filtrado en tiempo real, cambio de orden por columnas, badges de rol estilizados, estados de actividad y **navegador de pestañas entre Usuarios Activos y Papelera con badge interactivo**. Ofrece botones de restauración y eliminación definitiva mediante diálogos de confirmación.
- **`resources/js/Pages/Usuarios/Create.vue`**:
  Formulario de creación con validación reactiva de datos vía Inertia `useForm`.
- **`resources/js/Pages/Usuarios/Edit.vue`**:
  Formulario de actualización de datos de usuario con opción para cambio de contraseña y selección de rol.
- **`resources/js/Composables/useRoles.js`**:
  Composable que centraliza las etiquetas legibles, mapa de clases CSS para badges en modo claro/oscuro y lista de opciones para selectores.
