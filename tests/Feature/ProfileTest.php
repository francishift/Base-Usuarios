<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests de integración del módulo de perfil.
 * Rutas en castellano: /perfil (no /profile de Breeze por defecto).
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Deshabilitar CSRF en tests de mutación
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    #[Test]
    public function la_pagina_de_perfil_se_muestra_correctamente(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk();
    }

    #[Test]
    public function la_informacion_de_perfil_puede_actualizarse(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name'  => 'Nombre Actualizado',
                'email' => 'nuevo@ejemplo.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Nombre Actualizado', $user->name);
        $this->assertSame('nuevo@ejemplo.com', $user->email);
    }

    #[Test]
    public function el_estado_de_verificacion_no_cambia_si_el_email_es_el_mismo(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name'  => 'Nombre Actualizado',
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    #[Test]
    public function el_usuario_puede_eliminar_su_cuenta(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    #[Test]
    public function se_requiere_la_contrasena_correcta_para_eliminar_la_cuenta(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'contraseña-incorrecta',
            ])
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh());
    }
}
