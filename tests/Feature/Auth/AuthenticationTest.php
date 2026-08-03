<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests de integración de autenticación.
 * El registro público está desactivado en este proyecto.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    #[Test]
    public function la_pantalla_de_login_se_muestra_correctamente(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    #[Test]
    public function los_usuarios_pueden_autenticarse_con_credenciales_correctas(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    #[Test]
    public function los_usuarios_no_pueden_autenticarse_con_contrasena_incorrecta(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'contraseña-incorrecta',
        ]);

        $this->assertGuest();
    }

    #[Test]
    public function los_usuarios_desactivados_son_expulsados_al_acceder_a_rutas_protegidas(): void
    {
        // El middleware VerificarUsuarioActivo no bloquea el login en sí,
        // sino el acceso a rutas autenticadas cuando el usuario está inactivo.
        $user = User::factory()->inactivo()->create();

        // Intentar acceder al dashboard con un usuario desactivado
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    #[Test]
    public function el_registro_publico_esta_desactivado(): void
    {
        // La ruta GET /register existe pero redirige al login (registro capado).
        // No existe ruta POST /register — solo los administradores crean usuarios.
        $this->get(route('register'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function los_usuarios_pueden_cerrar_sesion(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
