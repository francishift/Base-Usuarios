<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Notifications\BienvenidaUsuario;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tests unitarios del UserService.
 *
 * Verifican la lógica de negocio de forma aislada:
 * creación, actualización, alternado de estado y eliminación de usuarios.
 */
class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpiamos la caché de permisos de Spatie entre tests
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Creamos los roles necesarios para los tests
        Role::firstOrCreate(['name' => 'admin',     'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'gestor',    'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'encargado', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'empleado',  'guard_name' => 'web']);

        $this->servicio = new UserService();
    }

    // -----------------------------------------------------------------------
    // crear()
    // -----------------------------------------------------------------------

    #[Test]
    public function crea_usuario_con_datos_correctos(): void
    {
        Notification::fake();

        $usuario = $this->servicio->crear([
            'name'  => 'Juan García',
            'email' => 'juan@ejemplo.com',
            'rol'   => 'empleado',
        ]);

        $this->assertDatabaseHas('users', [
            'name'   => 'Juan García',
            'email'  => 'juan@ejemplo.com',
            'active' => true,
        ]);
    }

    #[Test]
    public function crea_usuario_y_le_asigna_el_rol_correcto(): void
    {
        Notification::fake();

        $usuario = $this->servicio->crear([
            'name'  => 'María López',
            'email' => 'maria@ejemplo.com',
            'rol'   => 'gestor',
        ]);

        $this->assertTrue($usuario->hasRole('gestor'));
        $this->assertFalse($usuario->hasRole('admin'));
    }

    #[Test]
    public function crea_usuario_y_envia_notificacion_de_bienvenida(): void
    {
        Notification::fake();

        $usuario = $this->servicio->crear([
            'name'  => 'Pedro Sánchez',
            'email' => 'pedro@ejemplo.com',
            'rol'   => 'empleado',
        ]);

        Notification::assertSentTo($usuario, BienvenidaUsuario::class);
    }

    // -----------------------------------------------------------------------
    // actualizar()
    // -----------------------------------------------------------------------

    #[Test]
    public function actualiza_datos_del_usuario_correctamente(): void
    {
        $usuario = User::factory()->create([
            'name'  => 'Nombre Antiguo',
            'email' => 'antiguo@ejemplo.com',
        ]);
        $usuario->assignRole('empleado');

        $this->servicio->actualizar($usuario, [
            'name'  => 'Nombre Nuevo',
            'email' => 'nuevo@ejemplo.com',
            'rol'   => 'empleado',
        ]);

        $this->assertDatabaseHas('users', [
            'id'    => $usuario->id,
            'name'  => 'Nombre Nuevo',
            'email' => 'nuevo@ejemplo.com',
        ]);
    }

    #[Test]
    public function actualizar_sincroniza_el_rol_del_usuario(): void
    {
        $usuario = User::factory()->create();
        $usuario->assignRole('empleado');

        $this->servicio->actualizar($usuario, [
            'name'  => $usuario->name,
            'email' => $usuario->email,
            'rol'   => 'gestor',
        ]);

        $this->assertTrue($usuario->fresh()->hasRole('gestor'));
        $this->assertFalse($usuario->fresh()->hasRole('empleado'));
    }

    // -----------------------------------------------------------------------
    // alternarEstado()
    // -----------------------------------------------------------------------

    #[Test]
    public function alterna_estado_activo_a_inactivo(): void
    {
        $admin   = User::factory()->create(['active' => true]);
        $usuario = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $resultado = $this->servicio->alternarEstado($usuario);

        $this->assertFalse($resultado->active);
    }

    #[Test]
    public function alterna_estado_inactivo_a_activo(): void
    {
        $admin   = User::factory()->create(['active' => true]);
        $usuario = User::factory()->create(['active' => false]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $resultado = $this->servicio->alternarEstado($usuario);

        $this->assertTrue($resultado->active);
    }

    #[Test]
    public function no_permite_desactivar_la_propia_cuenta(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No puedes desactivar tu propia cuenta.');

        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $this->servicio->alternarEstado($admin);
    }

    // -----------------------------------------------------------------------
    // eliminar(), restaurar(), eliminarDefinitivo()
    // -----------------------------------------------------------------------

    #[Test]
    public function elimina_usuario_suavemente_soft_delete(): void
    {
        $admin   = User::factory()->create(['active' => true]);
        $usuario = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $usuarioId = $usuario->id;
        $this->servicio->eliminar($usuario);

        $this->assertSoftDeleted('users', ['id' => $usuarioId]);
    }

    #[Test]
    public function no_permite_eliminar_la_propia_cuenta(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No puedes eliminar tu propia cuenta.');

        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $this->servicio->eliminar($admin);
    }

    #[Test]
    public function restaura_usuario_de_la_papelera(): void
    {
        $admin   = User::factory()->create(['active' => true]);
        $usuario = User::factory()->create();
        $usuario->delete(); // Soft delete

        $this->actingAs($admin);
        $this->assertSoftDeleted('users', ['id' => $usuario->id]);

        $this->servicio->restaurar($usuario->id);

        $this->assertDatabaseHas('users', [
            'id'         => $usuario->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function elimina_definitivamente_un_usuario_de_la_papelera(): void
    {
        $admin   = User::factory()->create(['active' => true]);
        $usuario = User::factory()->create();
        $usuario->delete();

        $this->actingAs($admin);
        $this->assertSoftDeleted('users', ['id' => $usuario->id]);

        $this->servicio->eliminarDefinitivo($usuario->id);

        $this->assertDatabaseMissing('users', ['id' => $usuario->id]);
    }
}
