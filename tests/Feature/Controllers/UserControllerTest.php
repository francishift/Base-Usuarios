<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Notifications\BienvenidaUsuario;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tests de integración del UserController.
 *
 * Verifican el comportamiento completo de las rutas de gestión de usuarios:
 * control de acceso por rol, respuestas HTTP y persistencia en base de datos.
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $empleado;

    protected function setUp(): void
    {
        parent::setUp();

        // Deshabilitamos CSRF para todos los tests de este fichero
        // (estándar en tests de feature Laravel; CSRF no aplica en entorno de test)
        $this->withoutMiddleware(PreventRequestForgery::class);

        // Limpiamos la caché de permisos de Spatie entre tests
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Creamos los roles necesarios
        Role::firstOrCreate(['name' => 'admin',     'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'gestor',    'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'encargado', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'empleado',  'guard_name' => 'web']);

        // Usuario admin para los tests que requieren permisos
        $this->admin = User::factory()->create(['active' => true]);
        $this->admin->assignRole('admin');

        // Usuario sin permisos de admin
        $this->empleado = User::factory()->create(['active' => true]);
        $this->empleado->assignRole('empleado');
    }

    // -----------------------------------------------------------------------
    // Control de acceso
    // -----------------------------------------------------------------------

    #[Test]
    public function usuario_no_autenticado_no_puede_acceder_a_usuarios(): void
    {
        $this->get(route('usuarios.index'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function usuario_sin_rol_admin_no_puede_acceder_a_usuarios(): void
    {
        $this->actingAs($this->empleado)
            ->get(route('usuarios.index'))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // index()
    // -----------------------------------------------------------------------

    #[Test]
    public function admin_puede_ver_el_listado_de_usuarios(): void
    {
        $this->actingAs($this->admin)
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Usuarios/Index')
                ->has('usuarios')
            );
    }

    #[Test]
    public function el_listado_de_usuarios_esta_paginado(): void
    {
        // Creamos más usuarios que el límite de paginación (20)
        User::factory()->count(25)->create()->each(fn ($u) => $u->assignRole('empleado'));

        $this->actingAs($this->admin)
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Usuarios/Index')
                ->has('usuarios.data')
                ->has('usuarios.meta')
            );
    }

    // -----------------------------------------------------------------------
    // create() + store()
    // -----------------------------------------------------------------------

    #[Test]
    public function admin_puede_ver_el_formulario_de_creacion(): void
    {
        $this->actingAs($this->admin)
            ->get(route('usuarios.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Usuarios/Create')
                ->has('roles')
            );
    }

    #[Test]
    public function admin_puede_crear_un_usuario_nuevo(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('usuarios.store'), [
                'name'  => 'Nuevo Usuario',
                'email' => 'nuevo@ejemplo.com',
                'rol'   => 'empleado',
            ])
            ->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('users', [
            'name'  => 'Nuevo Usuario',
            'email' => 'nuevo@ejemplo.com',
        ]);
    }

    #[Test]
    public function crear_usuario_envia_notificacion_de_bienvenida(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('usuarios.store'), [
                'name'  => 'Test Notificación',
                'email' => 'notif@ejemplo.com',
                'rol'   => 'empleado',
            ]);

        $usuario = User::where('email', 'notif@ejemplo.com')->first();
        $this->assertNotNull($usuario, 'El usuario debería haberse creado');
        Notification::assertSentTo($usuario, BienvenidaUsuario::class);
    }

    #[Test]
    public function crear_usuario_valida_email_duplicado(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('usuarios.store'), [
                'name'  => 'Duplicado',
                'email' => $this->empleado->email,
                'rol'   => 'empleado',
            ])
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function crear_usuario_valida_rol_invalido(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('usuarios.store'), [
                'name'  => 'Inválido',
                'email' => 'invalido@ejemplo.com',
                'rol'   => 'rol_inexistente',
            ])
            ->assertSessionHasErrors('rol');
    }

    // -----------------------------------------------------------------------
    // edit() + update()
    // -----------------------------------------------------------------------

    #[Test]
    public function admin_puede_ver_el_formulario_de_edicion(): void
    {
        $this->actingAs($this->admin)
            ->get(route('usuarios.edit', $this->empleado))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Usuarios/Edit')
                ->has('usuario.id')   // Llega como objeto plano via ->resolve()
                ->has('roles')
            );
    }

    #[Test]
    public function admin_puede_actualizar_un_usuario(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('usuarios.update', $this->empleado), [
                'name'  => 'Nombre Actualizado',
                'email' => $this->empleado->email,
                'rol'   => 'gestor',
            ])
            ->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('users', [
            'id'   => $this->empleado->id,
            'name' => 'Nombre Actualizado',
        ]);

        $this->assertTrue($this->empleado->fresh()->hasRole('gestor'));
    }

    // -----------------------------------------------------------------------
    // toggleActive()
    // -----------------------------------------------------------------------

    #[Test]
    public function admin_puede_desactivar_un_usuario(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->patch(route('usuarios.toggle-active', $this->empleado))
            ->assertRedirect();

        $this->assertFalse($this->empleado->fresh()->active);
    }

    #[Test]
    public function admin_no_puede_desactivar_su_propia_cuenta(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->patch(route('usuarios.toggle-active', $this->admin))
            ->assertRedirect();

        // El admin sigue activo
        $this->assertTrue($this->admin->fresh()->active);
    }

    // -----------------------------------------------------------------------
    // destroy(), trashed(), restore(), forceDelete()
    // -----------------------------------------------------------------------

    #[Test]
    public function admin_puede_enviar_un_usuario_a_la_papelera(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('usuarios.destroy', $this->empleado))
            ->assertRedirect(route('usuarios.index'));

        $this->assertSoftDeleted('users', ['id' => $this->empleado->id]);
    }

    #[Test]
    public function admin_puede_ver_la_papelera_de_usuarios(): void
    {
        $this->empleado->delete();

        $this->actingAs($this->admin)
            ->get(route('usuarios.trashed'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Usuarios/Index')
                ->where('esPapelera', true)
                ->has('usuarios.data')
            );
    }

    #[Test]
    public function admin_puede_restaurar_un_usuario_de_la_papelera(): void
    {
        $this->empleado->delete();
        $this->assertSoftDeleted('users', ['id' => $this->empleado->id]);

        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->patch(route('usuarios.restore', $this->empleado->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'         => $this->empleado->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function admin_puede_eliminar_definitivamente_un_usuario_de_la_papelera(): void
    {
        $this->empleado->delete();
        $this->assertSoftDeleted('users', ['id' => $this->empleado->id]);

        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('usuarios.force-delete', $this->empleado->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $this->empleado->id]);
    }

    #[Test]
    public function admin_no_puede_eliminarse_a_si_mismo(): void
    {
        $adminId = $this->admin->id;

        $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('usuarios.destroy', $this->admin))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $adminId]);
    }
}
